<?php

namespace kalanis\kw_auth_sources\Sources\Files;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Data\FileCertUser;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Sources;
use kalanis\kw_locks\Interfaces\ILock;


/**
 * Class AccountsMultiFile
 * @package kalanis\kw_auth_sources\Sources\Files
 * Authenticate via multiple files
 */
class AccountsMultiFile implements Interfaces\IAuthCert, Interfaces\IWorkAccounts
{
    use Sources\TAuthLock;
    use Sources\TExpiration;
    use Sources\TLines;
    use Sources\TStatusTransform;

    const PW_NAME = 0;
    const PW_ID = 1;
    const PW_GROUP = 2;
    const PW_CLASS = 3;
    const PW_STATUS = 4;
    const PW_DISPLAY = 5;
    const PW_DIR = 6;
    const PW_FEED = 7;

    const SH_NAME = 0;
    const SH_PASS = 1;
    const SH_CHANGE_LAST = 2;
    const SH_CHANGE_NEXT = 3;
    const SH_CERT_SALT = 4;
    const SH_CERT_KEY = 5;
    const SH_FEED = 6;

    /** @var Storages\AStorage */
    protected $storage = null;
    /** @var Interfaces\IHashes */
    protected $mode = null;
    /** @var Interfaces\IStatus */
    protected $status = null;
    /** @var string[] */
    protected $path = [];

    /**
     * @param Storages\AStorage $storage
     * @param Interfaces\IHashes $mode
     * @param Interfaces\IStatus $status
     * @param ILock $lock
     * @param string[] $path
     * @param Interfaces\IKAusTranslations|null $lang
     */
    public function __construct(Storages\AStorage $storage, Interfaces\IHashes $mode, Interfaces\IStatus $status, ILock $lock, array $path, ?Interfaces\IKAusTranslations $lang = null)
    {
        $this->setAusLang($lang);
        $this->initAuthLock($lock);
        $this->storage = $storage;
        $this->path = $path;
        $this->mode = $mode;
        $this->status = $status;
    }

    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        if (empty($params['password'])) {
            throw new AuthSourcesException($this->getAusLang()->kauPassMustBeSet());
        }
        $time = time();
        $name = $this->stripChars($userName);

        // load from shadow
        $this->checkLock();

        try {
            $shadowLines = $this->openShadow();
        } catch (AuthSourcesException $ex) {
            // silence the problems on storage
            return null;
        }
        foreach ($shadowLines as &$line) {
            if (
                ($line[static::SH_NAME] == $name)
                && $this->mode->checkHash(strval($params['password']), strval($line[static::SH_PASS]))
                && ($time < $line[static::SH_CHANGE_NEXT])
            ) {
                $class = $this->getDataOnly($userName);
                if (
                    $class
                    && $this->status->allowLogin($class->getStatus())
                ) {
                    $this->setExpirationNotice($class, intval($line[static::SH_CHANGE_NEXT]));
                    return $class;
                }
            }
        }
        return null;
    }

    public function getDataOnly(string $userName): ?Interfaces\IUser
    {
        $name = $this->stripChars($userName);

        // load from password
        $this->checkLock();

        try {
            $passwordLines = $this->openPassword();
        } catch (AuthSourcesException $ex) {
            // silence the problems on storage
            return null;
        }
        foreach ($passwordLines as &$line) {
            if ($line[static::PW_NAME] == $name) {
                $user = $this->getUserClass();
                $user->setUserData(
                    strval($line[static::PW_ID]),
                    strval($line[static::PW_NAME]),
                    strval($line[static::PW_GROUP]),
                    intval($line[static::PW_CLASS]),
                    $this->transformFromStringToInt(strval($line[static::PW_STATUS])),
                    strval($line[static::PW_DISPLAY]),
                    strval($line[static::PW_DIR])
                );
                return $user;
            }
        }
        return null;
    }

    protected function getUserClass(): Interfaces\IUser
    {
        return new FileCertUser();
    }

    public function getCertData(string $userName): ?Interfaces\IUserCert
    {
        $name = $this->stripChars($userName);

        // load from shadow
        $this->checkLock();

        try {
            $shadowLines = $this->openShadow();
        } catch (AuthSourcesException $ex) {
            // silence the problems on storage
            return null;
        }
        foreach ($shadowLines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $class = $this->getDataOnly($userName);
                if (
                    $class
                    && ($class instanceof Interfaces\IUserCert)
                    && $this->status->allowCert($class->getStatus())
                ) {
                    $class->addCertInfo(
                        strval(base64_decode(strval($line[static::SH_CERT_KEY]))),
                        strval($line[static::SH_CERT_SALT])
                    );
                    return $class;
                }
            }
        }
        return null;
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        $name = $this->stripChars($userName);
        // load from shadow
        $this->checkLock();

        $changed = false;
        $this->getLock()->create();
        try {
            $lines = $this->openShadow();
        } finally {
            $this->getLock()->delete();
        }
        foreach ($lines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $changed = true;
                $line[static::SH_PASS] = $this->mode->createHash($passWord);
                $line[static::SH_CHANGE_NEXT] = $this->whenItExpire();
            }
        }

        $v2 = true;
        try {
            if ($changed) {
                $v2 = $this->saveShadow($lines);
            }
        } finally {
            $this->getLock()->delete();
        }
        return $changed && $v2;
    }

    public function updateCertKeys(string $userName, ?string $certKey, ?string $certSalt): bool
    {
        $name = $this->stripChars($userName);
        // load from shadow
        $this->checkLock();

        $changed = false;
        $this->getLock()->create();
        try {
            $lines = $this->openShadow();
        } finally {
            $this->getLock()->delete();
        }
        foreach ($lines as &$line) {
            if ($line[static::SH_NAME] == $name) {
                $changed = true;
                $line[static::SH_CERT_KEY] = $certKey ? base64_encode($certKey) : $line[static::SH_CERT_KEY];
                $line[static::SH_CERT_SALT] = $certSalt ?? $line[static::SH_CERT_SALT];
            }
        }

        $v2 = true;
        try {
            if ($changed) {
                $v2 = $this->saveShadow($lines);
            }
        } finally {
            $this->getLock()->delete();
        }
        return $changed && $v2;
    }

    public function createAccount(Interfaces\IUser $user, string $password): bool
    {
        $userName = $this->stripChars($user->getAuthName());
        $displayName = $this->stripChars($user->getDisplayName());
        $directory = $this->stripChars($user->getDir());
        $certSalt = '';
        $certKey = '';

        if ($user instanceof Interfaces\IUserCert) {
            $certSalt = $this->stripChars($user->getPubSalt());
            $certKey = $user->getPubKey();
        }

        // not everything necessary is set
        if (empty($userName) || empty($directory) || empty($password)) {
            throw new AuthSourcesException($this->getAusLang()->kauPassMissParam());
        }
        $this->checkLock();

        $uid = Interfaces\IUser::LOWEST_USER_ID;
        $this->getLock()->create();

        // read password
        try {
            $passLines = $this->openPassword();
        } catch (AuthSourcesException $ex) {
            $passLines = [];
        }
        foreach ($passLines as &$line) {
            $uid = max($uid, $line[static::PW_ID]);
        }
        $uid++;

        $newUserPass = [
            static::PW_NAME => $userName,
            static::PW_ID => $uid,
            static::PW_GROUP => empty($user->getGroup()) ? $uid : $user->getGroup() ,
            static::PW_CLASS => empty($user->getClass()) ? Interfaces\IWorkClasses::CLASS_USER : $user->getClass() ,
            static::PW_STATUS => $this->transformFromIntToString($user->getStatus()),
            static::PW_DISPLAY => empty($displayName) ? $userName : $displayName,
            static::PW_DIR => $directory,
            static::PW_FEED => '',
        ];
        ksort($newUserPass);
        $passLines[] = $newUserPass;

        // now read shadow
        try {
            $shadeLines = $this->openShadow();
        } catch (AuthSourcesException $ex) {
            $shadeLines = [];
        }

        $newUserShade = [
            static::SH_NAME => $userName,
            static::SH_PASS => $this->mode->createHash($password),
            static::SH_CHANGE_LAST => time(),
            static::SH_CHANGE_NEXT => $this->whenItExpire(),
            static::SH_CERT_SALT => $certSalt,
            static::SH_CERT_KEY => $certKey ? base64_encode($certKey) : '',
            static::SH_FEED => '',
        ];
        ksort($newUserShade);
        $shadeLines[] = $newUserShade;

        // now save it all
        $v1 = $v2 = true;
        try {
            $v1 = $this->savePassword($passLines);
            $v2 = $this->saveShadow($shadeLines);
        } finally {
            $this->getLock()->delete();
        }
        return $v1 && $v2;
    }

    public function readAccounts(): array
    {
        $this->checkLock();

        $passLines = $this->openPassword();
        $result = [];
        foreach ($passLines as &$line) {
            $record = $this->getUserClass();
            $record->setUserData(
                strval($line[static::PW_ID]),
                strval($line[static::PW_NAME]),
                strval($line[static::PW_GROUP]),
                intval($line[static::PW_CLASS]),
                $this->transformFromStringToInt(strval($line[static::PW_STATUS])),
                strval($line[static::PW_DISPLAY]),
                strval($line[static::PW_DIR])
            );
            $result[] = $record;
        }

        return $result;
    }

    public function updateAccount(Interfaces\IUser $user): bool
    {
        $userName = $this->stripChars($user->getAuthName());
        $directory = $this->stripChars($user->getDir());
        $displayName = $this->stripChars($user->getDisplayName());

        $this->checkLock();

        $this->getLock()->create();
        $oldName = null;
        try {
            $passwordLines = $this->openPassword();
        } finally {
            $this->getLock()->delete();
        }
        foreach ($passwordLines as &$line) {
            if (($line[static::PW_NAME] == $userName) && ($line[static::PW_ID] != $user->getAuthId())) {
                $this->getLock()->delete();
                throw new AuthSourcesException($this->getAusLang()->kauPassLoginExists());
            }
            if ($line[static::PW_ID] == $user->getAuthId()) {
                // REFILL
                if (!empty($userName) && $userName != $line[static::PW_NAME]) {
                    $oldName = $line[static::PW_NAME];
                    $line[static::PW_NAME] = $userName;
                }
                $line[static::PW_GROUP] = !empty($user->getGroup()) ? $user->getGroup() : $line[static::PW_GROUP] ;
                $line[static::PW_CLASS] = !empty($user->getClass()) ? $user->getClass() : $line[static::PW_CLASS] ;
                $line[static::PW_STATUS] = $this->transformFromIntToString($user->getStatus());
                $line[static::PW_DISPLAY] = !empty($displayName) ? $displayName : $line[static::PW_DISPLAY] ;
                $line[static::PW_DIR] = !empty($directory) ? $directory : $line[static::PW_DIR] ;
            }
        }

        $v2 = $v1 = true;
        try {
            $v1 = $this->savePassword($passwordLines);

            if (!is_null($oldName)) {
                $lines = $this->openShadow();
                foreach ($lines as &$line) {
                    if ($line[static::SH_NAME] == $oldName) {
                        $line[static::SH_NAME] = $userName;
                    }
                }
                $v2 = $this->saveShadow($lines);
            }
        } finally {
            $this->getLock()->delete();
        }
        return $v1 && $v2;
    }

    public function deleteAccount(string $userName): bool
    {
        $name = $this->stripChars($userName);
        $this->checkLock();

        $changed = false;
        $this->getLock()->create();

        // update password
        try {
            $passLines = $this->openPassword();
        } finally {
            $this->getLock()->delete();
        }
        foreach ($passLines as $index => &$line) {
            if ($line[static::PW_NAME] == $name) {
                unset($passLines[$index]);
                $changed = true;
            }
        }

        // now update shadow
        try {
            $shadeLines = $this->openShadow();
        } finally {
            $this->getLock()->delete();
        }
        foreach ($shadeLines as $index => &$line) {
            if ($line[static::SH_NAME] == $name) {
                unset($shadeLines[$index]);
                $changed = true;
            }
        }

        // now save it all
        $v1 = $v2 = true;
        try {
            if ($changed) {
                $v1 = $this->savePassword($passLines);
                $v2 = $this->saveShadow($shadeLines);
            }
        } finally {
            $this->getLock()->delete();
        }
        return $changed && $v1 && $v2;
    }

    /**
     * @throws AuthSourcesException
     * @return array<int, array<int, string|int>>
     */
    protected function openPassword(): array
    {
        return $this->storage->read(array_merge($this->path, [Interfaces\IFile::PASS_FILE]));
    }

    /**
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthSourcesException
     * @return bool
     */
    protected function savePassword(array $lines): bool
    {
        return $this->storage->write(array_merge($this->path, [Interfaces\IFile::PASS_FILE]), $lines);
    }

    /**
     * @throws AuthSourcesException
     * @return array<int, array<int, string|int>>
     */
    protected function openShadow(): array
    {
        return $this->storage->read(array_merge($this->path, [Interfaces\IFile::SHADE_FILE]));
    }

    /**
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthSourcesException
     * @return bool
     */
    protected function saveShadow(array $lines): bool
    {
        return $this->storage->write(array_merge($this->path, [Interfaces\IFile::SHADE_FILE]), $lines);
    }
}

<?php

namespace kalanis\kw_auth_sources\Sources\Files;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Data\FileCertUser;
use kalanis\kw_accounts\Interfaces as acc_interfaces;
use kalanis\kw_accounts\Traits as account_traits;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Traits;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Class AccountsMultiFile
 * @package kalanis\kw_auth_sources\Sources\Files
 * Authenticate via multiple files
 */
class AccountsMultiFile implements acc_interfaces\IAuthCert, acc_interfaces\IProcessAccounts
{
    use Traits\TAuthLock;
    use Traits\TLines;
    use Traits\TStatusTransform;
    use account_traits\TExpiration;

    protected const PW_NAME = 0;
    protected const PW_ID = 1;
    protected const PW_GROUP = 2;
    protected const PW_CLASS = 3;
    protected const PW_STATUS = 4;
    protected const PW_DISPLAY = 5;
    protected const PW_DIR = 6;
    protected const PW_EXTRA = 7;
    protected const PW_FEED = 8;

    protected const SH_NAME = 0;
    protected const SH_PASS = 1;
    protected const SH_CHANGE_LAST = 2;
    protected const SH_CHANGE_NEXT = 3;
    protected const SH_CERT_SALT = 4;
    protected const SH_CERT_KEY = 5;
    protected const SH_FEED = 6;

    protected Storages\AStorage $storage;
    protected Interfaces\IHashes $mode;
    protected Interfaces\IStatus $status;
    protected Interfaces\IExtraParser $extraParser;
    /** @var string[] */
    protected array $path = [];

    /**
     * @param Storages\AStorage $storage
     * @param Interfaces\IHashes $mode
     * @param Interfaces\IStatus $status
     * @param Interfaces\IExtraParser $parser
     * @param ILock $lock
     * @param string[] $path
     * @param Interfaces\IKAusTranslations|null $lang
     */
    public function __construct(
        Storages\AStorage $storage,
        Interfaces\IHashes $mode,
        Interfaces\IStatus $status,
        Interfaces\IExtraParser $parser,
        ILock $lock,
        array $path,
        ?Interfaces\IKAusTranslations $lang = null
    )
    {
        $this->setAusLang($lang);
        $this->initAuthLock($lock);
        $this->storage = $storage;
        $this->path = $path;
        $this->mode = $mode;
        $this->status = $status;
        $this->extraParser = $parser;
    }

    public function authenticate(string $userName, array $params = []): ?acc_interfaces\IUser
    {
        if (empty($params['password'])) {
            throw new AccountsException($this->getAusLang()->kauPassMustBeSet());
        }
        $time = time();
        $name = $this->stripChars($userName);

        try {
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
        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function getDataOnly(string $userName): ?acc_interfaces\IUser
    {
        $name = $this->stripChars($userName);

        try {
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
                        strval($line[static::PW_DIR]),
                        $this->extraParser->expand(strval($line[static::PW_EXTRA]))
                    );
                    return $user;
                }
            }
            return null;
        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    protected function getUserClass(): acc_interfaces\IUser
    {
        return new FileCertUser();
    }

    public function getCertData(string $userName): ?acc_interfaces\ICert
    {
        $name = $this->stripChars($userName);

        try {
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
                        && ($class instanceof acc_interfaces\IUserCert)
                        && $this->status->allowCert($class->getStatus())
                    ) {
                        $class->updateCertInfo(
                            strval(base64_decode(strval($line[static::SH_CERT_KEY]))),
                            strval($line[static::SH_CERT_SALT])
                        );
                        return $class;
                    }
                }
            }
            return null;
        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        $name = $this->stripChars($userName);

        try {
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

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function updateCertData(string $userName, ?string $certKey, ?string $certSalt): bool
    {
        $name = $this->stripChars($userName);

        try {
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

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function createAccount(acc_interfaces\IUser $user, string $password): bool
    {
        $userName = $this->stripChars($user->getAuthName());
        $displayName = $this->stripChars($user->getDisplayName());
        $directory = $this->stripChars($user->getDir());
        $certSalt = '';
        $certKey = '';

        if ($user instanceof acc_interfaces\IUserCert) {
            $certSalt = $this->stripChars($user->getSalt());
            $certKey = $user->getPubKey();
        }

        // not everything necessary is set
        if (empty($userName) || empty($directory) || empty($password)) {
            throw new AccountsException($this->getAusLang()->kauPassMissParam());
        }

        try {
            $this->checkLock();

            $uid = acc_interfaces\IUser::LOWEST_USER_ID;
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
                static::PW_CLASS => empty($user->getClass()) ? acc_interfaces\IProcessClasses::CLASS_USER : $user->getClass() ,
                static::PW_STATUS => $this->transformFromIntToString($user->getStatus()),
                static::PW_DISPLAY => empty($displayName) ? $userName : $displayName,
                static::PW_DIR => $directory,
                static::PW_EXTRA => $this->extraParser->compact($user->getExtra()),
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

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function readAccounts(): array
    {
        try {
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
                    strval($line[static::PW_DIR]),
                    $this->extraParser->expand(strval($line[static::PW_EXTRA]))
                );
                $result[] = $record;
            }

            return $result;

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function updateAccount(acc_interfaces\IUser $user): bool
    {
        $userName = $this->stripChars($user->getAuthName());
        $directory = $this->stripChars($user->getDir());
        $displayName = $this->stripChars($user->getDisplayName());

        try {
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
                    $line[static::PW_EXTRA] = !empty($user->getExtra()) ? $this->extraParser->compact($user->getExtra()) : $line[static::PW_EXTRA] ;
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

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function deleteAccount(string $userName): bool
    {
        $name = $this->stripChars($userName);

        try {
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

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
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

    /**
     * @return string
     * @codeCoverageIgnore translation
     */
    protected function noDirectoryDelimiterSet(): string
    {
        return $this->getAusLang()->kauNoDelimiterSet();
    }
}

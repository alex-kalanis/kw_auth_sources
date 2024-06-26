<?php

namespace kalanis\kw_auth_sources\Sources\Files;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Data\FileUser;
use kalanis\kw_accounts\Interfaces as acc_interfaces;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Traits;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Class AccountsSingleFile
 * @package kalanis\kw_auth_sources\Sources\Files
 * Authenticate via single file
 */
class AccountsSingleFile implements acc_interfaces\IAuth, acc_interfaces\IProcessAccounts
{
    use Traits\TAuthLock;
    use Traits\TLines;
    use Traits\TStatusTransform;

    protected const PW_ID = 0;
    protected const PW_NAME = 1;
    protected const PW_PASS = 2;
    protected const PW_GROUP = 3;
    protected const PW_CLASS = 4;
    protected const PW_STATUS = 5;
    protected const PW_DISPLAY = 6;
    protected const PW_DIR = 7;
    protected const PW_EXTRA = 8;
    protected const PW_FEED = 9;

    protected Storages\AStorage $storage;
    protected Interfaces\IHashes $mode;
    protected Interfaces\IStatus $status;
    protected Interfaces\IExtraParser $extraParser;
    /** @var string[] */
    protected array $path = [];

    /**
     * @param Storages\AStorage $storage where is it stored and how to access there
     * @param Interfaces\IHashes $mode hashing mode
     * @param Interfaces\IStatus $status which status is necessary to use that feature
     * @param Interfaces\IExtraParser $parser parsing extra arguments from and to string
     * @param ILock $lock file lock
     * @param string[] $path use full path with file name
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
        $this->mode = $mode;
        $this->status = $status;
        $this->path = $path;
        $this->extraParser = $parser;
    }

    public function authenticate(string $userName, array $params = []): ?acc_interfaces\IUser
    {
        if (empty($params['password'])) {
            throw new AccountsException($this->getAusLang()->kauPassMustBeSet());
        }
        $name = $this->stripChars($userName);
        $pass = strval($params['password']);

        try {
            $this->checkLock();
            try {
                $passLines = $this->openPassword();
            } catch (AuthSourcesException $ex) {
                // silence the problems on storage
                return null;
            }
            foreach ($passLines as &$line) {
                if ($line[static::PW_NAME] == $name) {
                    if (
                        $this->mode->checkHash($pass, strval($line[static::PW_PASS]))
                        && $this->status->allowLogin($this->transformFromStringToInt(strval($line[static::PW_STATUS])))
                    ) {
                        return $this->getUserClass($line);
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

        // load from password
        try {
            $this->checkLock();
            try {
                $passwordLines = $this->openPassword();
            } catch (AuthSourcesException $ex) {
                // silence the problems on storage
                return null;
            }
            foreach ($passwordLines as &$line) {
                if ($line[static::PW_NAME] == $name) {
                    return $this->getUserClass($line);
                }
            }
            return null;

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @param array<int, string|int|float> $line
     * @throws AuthSourcesException
     * @return acc_interfaces\IUser
     */
    protected function getUserClass(array &$line): acc_interfaces\IUser
    {
        $user = new FileUser();
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

    public function createAccount(acc_interfaces\IUser $user, string $password): bool
    {
        $userName = $this->stripChars($user->getAuthName());
        $directory = $this->stripChars($user->getDir());
        $displayName = $this->stripChars($user->getDisplayName());

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
                // silence the problems on storage
                $passLines = [];
            }
            foreach ($passLines as &$line) {
                $uid = max($uid, intval($line[static::PW_ID]));
            }
            $uid++;

            $newUserPass = [
                static::PW_ID => strval($uid),
                static::PW_NAME => $userName,
                static::PW_PASS => $this->mode->createHash($password),
                static::PW_GROUP => empty($user->getGroup()) ? $uid : $user->getGroup() ,
                static::PW_CLASS => empty($user->getClass()) ? acc_interfaces\IProcessClasses::CLASS_USER : strval($user->getClass()) ,
                static::PW_STATUS => $this->transformFromIntToString($user->getStatus()),
                static::PW_DISPLAY => empty($displayName) ? $userName : $displayName,
                static::PW_DIR => $directory,
                static::PW_EXTRA => $this->extraParser->compact($user->getExtra()),
                static::PW_FEED => '',
            ];
            ksort($newUserPass);
            $passLines[] = $newUserPass;

            // now save it
            try {
                $result = $this->savePassword($passLines);
            } finally {
                $this->getLock()->delete();
            }
            return $result;

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
                $result[] = $this->getUserClass($line);
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
            try {
                $passwordLines = $this->openPassword();
            } finally {
                $this->getLock()->delete();
            }
            foreach ($passwordLines as &$line) {
                if ($line[static::PW_NAME] == $userName) {
                    // REFILL
                    $line[static::PW_GROUP] = !empty($user->getGroup()) ? $user->getGroup() : $line[static::PW_GROUP] ;
                    $line[static::PW_CLASS] = !empty($user->getClass()) ? strval($user->getClass()) : $line[static::PW_CLASS] ;
                    $line[static::PW_STATUS] = $this->transformFromIntToString($user->getStatus());
                    $line[static::PW_DISPLAY] = !empty($displayName) ? $displayName : $line[static::PW_DISPLAY] ;
                    $line[static::PW_DIR] = !empty($directory) ? $directory : $line[static::PW_DIR] ;
                    $line[static::PW_EXTRA] = !empty($user->getExtra()) ? $this->extraParser->compact($user->getExtra()) : $line[static::PW_EXTRA] ;
                }
            }

            try {
                $result = $this->savePassword($passwordLines);
            } finally {
                $this->getLock()->delete();
            }
            return $result;

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        $name = $this->stripChars($userName);
        // load from shadow
        try {
            $this->checkLock();

            $changed = false;
            $this->getLock()->create();

            try {
                $lines = $this->openPassword();
            } finally {
                $this->getLock()->delete();
            }
            foreach ($lines as &$line) {
                if ($line[static::PW_NAME] == $name) {
                    $changed = true;
                    $line[static::PW_PASS] = $this->mode->createHash($passWord);
                }
            }
            try {
                if ($changed) {
                    $this->savePassword($lines);
                }
            } finally {
                $this->getLock()->delete();
            }
            return true;

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
            } catch (AuthSourcesException $ex) {
                // removal on non-existent file is not possible and not necessary
                $this->getLock()->delete();
                return true;
            }

            foreach ($passLines as $index => &$line) {
                if ($line[static::PW_NAME] == $name) {
                    unset($passLines[$index]);
                    $changed = true;
                }
            }

            // now save it all
            try {
                if ($changed) {
                    $this->savePassword($passLines);
                }
            } finally {
                $this->getLock()->delete();
            }
            return true;

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
        return $this->storage->read($this->path);
    }

    /**
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthSourcesException
     * @return bool
     */
    protected function savePassword(array $lines): bool
    {
        return $this->storage->write($this->path, $lines);
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

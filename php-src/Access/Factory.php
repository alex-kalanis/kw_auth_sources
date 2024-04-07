<?php

namespace kalanis\kw_auth_sources\Access;


use kalanis\kw_accounts\Interfaces as acc_interfaces;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\ExtraParsers;
use kalanis\kw_auth_sources\Hashes;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Statuses;
use kalanis\kw_auth_sources\Sources;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_files\Access\CompositeAdapter;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_locks\Interfaces\IKLTranslations;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;
use kalanis\kw_locks\Methods as lock_methods;
use kalanis\kw_storage\Access as storage_access;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\StorageException;


/**
 * Class Factory
 * @package kalanis\kw_auth_sources\Access
 */
class Factory
{
    use TLang;

    public function __construct(?Interfaces\IKAusTranslations $lang = null)
    {
        $this->setAusLang($lang);
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>>|string|object|int|bool|null $params
     * @throws AuthSourcesException
     * @throws LockException
     * @return CompositeSources
     */
    public function getSources($params): CompositeSources
    {
        if (is_object($params)) {
            if ($params instanceof CompositeSources) {
                return $params;
            }
            if ($params instanceof SourcesAdapters\AAdapter) {
                return $this->getCompositeSourceInstance($params);
            }
            if ($params instanceof IStorage) {
                $storage = new Sources\Files\Storages\Storage($params, $this->getAusLang());
                $lock = new lock_methods\StorageLock($params);
                $accounts = new Sources\Files\AccountsSingleFile(
                    $storage,
                    new Hashes\CoreLib(),
                    new Statuses\Always(),
                    new ExtraParsers\Serialize(),
                    $lock,
                    [],
                    $this->getAusLang()
                );
                return $this->getCompositeSourceInstance(new SourcesAdapters\Direct(
                    $accounts,
                    $accounts,
                    new Sources\Files\Groups(
                        $storage,
                        $accounts,
                        new ExtraParsers\Serialize(),
                        $lock,
                        [],
                        $this->getAusLang()
                    ),
                    new Sources\Classes()
                ));
            }
            if ($params instanceof CompositeAdapter) {
                $storage = new Sources\Files\Storages\Files($params, $this->getAusLang());
                $lock = new lock_methods\FilesLock($params);
                $accounts = new Sources\Files\AccountsSingleFile(
                    $storage,
                    new Hashes\CoreLib(),
                    new Statuses\Always(),
                    new ExtraParsers\Serialize(),
                    $lock,
                    [],
                    $this->getAusLang()
                );
                return $this->getCompositeSourceInstance(new SourcesAdapters\Direct(
                    $accounts,
                    $accounts,
                    new Sources\Files\Groups(
                        $storage,
                        $accounts,
                        new ExtraParsers\Serialize(),
                        $lock,
                        [],
                        $this->getAusLang()
                    ),
                    new Sources\Classes()
                ));
            }
        } elseif (is_string($params)) {
            if ('ldap' == $params) {
                return $this->getCompositeSourceInstance(new SourcesAdapters\Direct(
                    new Sources\Mapper\AuthLdap(),
                    new Sources\Dummy\Accounts(),
                    new Sources\Dummy\Groups(),
                    new Sources\Classes()
                ));
            }
            if ('db' == $params) {
                $auth = new Sources\Mapper\AccountsDatabase(new Hashes\CoreLib());
                return $this->getCompositeSourceInstance(new SourcesAdapters\Direct(
                    $auth,
                    $auth,
                    new Sources\Mapper\GroupsDatabase(),
                    new Sources\Classes()
                ));
            }
            if (($dir = realpath($params)) && is_dir($params)) {
                $storage = new Sources\Files\Storages\Volume($dir . DIRECTORY_SEPARATOR, $this->getAusLang());
                $lock = new lock_methods\FileLock($dir . DIRECTORY_SEPARATOR . 'sources.lock');
                $accounts = new Sources\Files\AccountsSingleFile(
                    $storage,
                    new Hashes\CoreLib(),
                    new Statuses\Always(),
                    new ExtraParsers\Serialize(),
                    $lock,
                    [],
                    $this->getAusLang()
                );
                return $this->getCompositeSourceInstance(new SourcesAdapters\Direct(
                    $accounts,
                    $accounts,
                    new Sources\Files\Groups(
                        $storage,
                        $accounts,
                        new ExtraParsers\Serialize(),
                        $lock,
                        [],
                        $this->getAusLang()
                    ),
                    new Sources\Classes()
                ));
            }
        } elseif (is_array($params)) {
            // now it became a bit complicated...
            if (isset($params['path']) && is_string($params['path'])) {
                $params['storage'] = $params['path'];
                $params['status'] = true;
                unset($params['path']);
            }
            if (isset($params['source']) && is_string($params['source'])) {
                $params['storage'] = $params['source'];
                $params['status'] = true;
                unset($params['source']);
            }
            $storage = $this->whichStorage($params);
            $hash = $this->whichHash($params);
            $status = $this->whichStatus($params);
            $lock = $this->whichLocks($params);
            $extraParser = $this->whichParser($params);
            $accounts = $this->getAccounts($params, $storage, $hash, $status, $extraParser, $lock);
            $auth = ($accounts instanceof acc_interfaces\IAuth) ? $accounts : $this->getAuth($params, $storage, $hash, $status, $extraParser, $lock);
            return $this->getCompositeSourceInstance(new SourcesAdapters\Direct(
                $auth,
                $accounts,
                $this->getGroups($params, $storage, $accounts, $extraParser, $lock),
                $this->getClasses($params)
            ));
        }
        throw new AuthSourcesException($this->getAusLang()->kauCombinationUnavailable());
    }

    protected function getCompositeSourceInstance(SourcesAdapters\AAdapter $adapter): CompositeSources
    {
        return new CompositeSources($adapter);
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @throws AuthSourcesException
     * @return Sources\Files\Storages\AStorage
     */
    protected function whichStorage(array $params): Sources\Files\Storages\AStorage
    {
        if (isset($params['storage'])) {
            if (is_object($params['storage']) && ($params['storage'] instanceof Sources\Files\Storages\AStorage)) {
                return $params['storage'];
            }
            if (is_object($params['storage']) && ($params['storage'] instanceof IStorage)) {
                return new Sources\Files\Storages\Storage($params['storage'], $this->getAusLang());
            }
            if (is_object($params['storage']) && ($params['storage'] instanceof CompositeAdapter)) {
                return new Sources\Files\Storages\Files($params['storage'], $this->getAusLang());
            }
            if (is_array($params['storage'])) {
                try {
                    return new Sources\Files\Storages\Storage(storage_access\MultitonInstances::getInstance()->lookup($params['storage']), $this->getAusLang());
                } catch (StorageException $ex) {
                    throw new AuthSourcesException($ex->getMessage(), $ex->getCode(), $ex);
                }
            }
            if (is_string($params['storage']) && ($pt = realpath($params['storage']))) {
                return new Sources\Files\Storages\Volume($pt . DIRECTORY_SEPARATOR, $this->getAusLang());
            }
        }
        throw new AuthSourcesException($this->getAusLang()->kauCombinationUnavailable());
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @return Interfaces\IHashes
     */
    protected function whichHash(array $params): Interfaces\IHashes
    {
        if (isset($params['hash'])) {
            if (is_object($params['hash']) && $params['hash'] instanceof Interfaces\IHashes) {
                return $params['hash'];
            }
            if (is_string($params['hash'])) {
                return new Hashes\KwOrig($params['hash'], $this->getAusLang());
            }
        }
        return new Hashes\CoreLib();
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @throws AuthSourcesException
     * @return Interfaces\IStatus
     */
    protected function whichStatus(array $params): Interfaces\IStatus
    {
        if (isset($params['status'])) {
            if (is_object($params['status']) && $params['status'] instanceof Interfaces\IStatus) {
                return $params['status'];
            }
            if (is_string($params['status'])) {
                return ('always' == $params['status']) ? new Statuses\Always() : new Statuses\Checked();
            }
            if (is_numeric($params['status'])) {
                return boolval(intval($params['status'])) ? new Statuses\Always() : new Statuses\Checked();
            }
            if (is_bool($params['status'])) {
                return $params['status'] ? new Statuses\Always() : new Statuses\Checked();
            }
        }
        throw new AuthSourcesException($this->getAusLang()->kauCombinationUnavailable());
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @return Interfaces\IExtraParser
     */
    protected function whichParser(array $params): Interfaces\IExtraParser
    {
        if (isset($params['parser'])) {
            if (is_object($params['parser']) && $params['parser'] instanceof Interfaces\IExtraParser) {
                return $params['parser'];
            }
            if (is_string($params['parser'])) {
                if ('php' == $params['parser']) {
                    return new ExtraParsers\Serialize();
                }
                if ('serial' == $params['parser']) {
                    return new ExtraParsers\Serialize();
                }
                if ('none' == $params['parser']) {
                    return new ExtraParsers\None();
                }
                return new ExtraParsers\Json();
            }
            if (is_numeric($params['parser'])) {
                return boolval(intval($params['parser'])) ? new ExtraParsers\Serialize() : new ExtraParsers\Json();
            }
            if (is_bool($params['parser'])) {
                return $params['parser'] ? new ExtraParsers\Serialize() : new ExtraParsers\Json();
            }
        }
        return new ExtraParsers\None();
//        throw new AuthSourcesException($this->getAusLang()->kauCombinationUnavailable());
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @throws AuthSourcesException
     * @return ILock
     */
    protected function whichLocks(array $params): ILock
    {
        try {
            $lockLang = null;
            if (isset($params['lock_lang']) && ($params['lock_lang'] instanceof IKLTranslations)) {
                $lockLang = $params['lock_lang'];
            }
            if (!$lockLang && (($localLang = $this->getAusLang()) instanceof IKLTranslations)) {
                $lockLang = $localLang;
            }
            if (isset($params['lock'])) {
                if (is_object($params['lock']) && ($params['lock'] instanceof ILock)) {
                    return $params['lock'];
                }
                if (is_object($params['lock']) && ($params['lock'] instanceof IStorage)) {
                    return new lock_methods\StorageLock($params['lock'], $lockLang);
                }
                if (is_object($params['lock']) && ($params['lock'] instanceof CompositeAdapter)) {
                    return new lock_methods\FilesLock($params['lock'], $lockLang);
                }
                if (is_string($params['lock']) && ($pt = realpath($params['lock']))) {
                    return new lock_methods\FileLock($pt . DIRECTORY_SEPARATOR . 'auth.lock', $lockLang);
                }
            } elseif (isset($params['storage'])) {
                if (is_object($params['storage']) && ($params['storage'] instanceof ILock)) {
                    return $params['storage'];
                }
                if (is_object($params['storage']) && ($params['storage'] instanceof IStorage)) {
                    return new lock_methods\StorageLock($params['storage'], $lockLang);
                }
                if (is_object($params['storage']) && ($params['storage'] instanceof CompositeAdapter)) {
                    return new lock_methods\FilesLock($params['storage'], $lockLang);
                }
                if (is_string($params['storage']) && ($pt = realpath($params['storage']))) {
                    return new lock_methods\FileLock($pt . DIRECTORY_SEPARATOR . 'auth.lock', $lockLang);
                }
            }
            // @codeCoverageIgnoreStart
        } catch (LockException $ex) {
            // dies FileLock
            throw new AuthSourcesException($ex->getMessage(), $ex->getCode(), $ex);
        }
        // @codeCoverageIgnoreEnd
        throw new AuthSourcesException($this->getAusLang()->kauCombinationUnavailable());
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @param Sources\Files\Storages\AStorage $storage
     * @param Interfaces\IHashes $hash
     * @param Interfaces\IStatus $status
     * @param Interfaces\IExtraParser $parser
     * @param ILock $lock
     * @return acc_interfaces\IAuth
     */
    protected function getAuth(
        array $params,
        Sources\Files\Storages\AStorage $storage,
        Interfaces\IHashes $hash,
        Interfaces\IStatus $status,
        Interfaces\IExtraParser $parser,
        ILock $lock
    ): acc_interfaces\IAuth
    {
        if (isset($params['auth']) && ($params['auth'] instanceof acc_interfaces\IAuth)) {
            return $params['auth'];
        }
        if (isset($params['single_file'])) {
            return new Sources\Files\AccountsSingleFile($storage, $hash, $status, $parser, $lock, $this->clearedPath($params), $this->getAusLang());
        }
        return new Sources\Files\AccountsMultiFile($storage, $hash, $status, $parser, $lock, $this->clearedPath($params), $this->getAusLang());
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @param Sources\Files\Storages\AStorage $storage
     * @param Interfaces\IHashes $hash
     * @param Interfaces\IStatus $status
     * @param Interfaces\IExtraParser $parser
     * @param ILock $lock
     * @return acc_interfaces\IProcessAccounts
     */
    protected function getAccounts(
        array $params,
        Sources\Files\Storages\AStorage $storage,
        Interfaces\IHashes $hash,
        Interfaces\IStatus $status,
        Interfaces\IExtraParser $parser,
        ILock $lock
    ): acc_interfaces\IProcessAccounts
    {
        if (isset($params['accounts']) && ($params['accounts'] instanceof acc_interfaces\IProcessAccounts)) {
            return $params['accounts'];
        }
        if (isset($params['single_file'])) {
            return new Sources\Files\AccountsSingleFile($storage, $hash, $status, $parser, $lock, $this->clearedPath($params), $this->getAusLang());
        }
        return new Sources\Files\AccountsMultiFile($storage, $hash, $status, $parser, $lock, $this->clearedPath($params), $this->getAusLang());
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @param Sources\Files\Storages\AStorage $storage
     * @param acc_interfaces\IProcessAccounts $accounts
     * @param Interfaces\IExtraParser $parser
     * @param ILock $lock
     * @return acc_interfaces\IProcessGroups
     */
    protected function getGroups(
        array $params,
        Sources\Files\Storages\AStorage $storage,
        acc_interfaces\IProcessAccounts $accounts,
        Interfaces\IExtraParser $parser,
        ILock $lock
    ): acc_interfaces\IProcessGroups
    {
        if (isset($params['groups']) && ($params['groups'] instanceof acc_interfaces\IProcessGroups)) {
            return $params['groups'];
        }
        return new Sources\Files\Groups($storage, $accounts, $parser, $lock, $this->clearedPath($params), $this->getAusLang());
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @return acc_interfaces\IProcessClasses
     */
    protected function getClasses(array $params): acc_interfaces\IProcessClasses
    {
        if (isset($params['classes']) && ($params['classes'] instanceof acc_interfaces\IProcessClasses)) {
            return $params['classes'];
        }
        return new Sources\Classes();
    }

    /**
     * @param array<string|int, string|int|float|object|bool|array<string|int|float|object>> $params
     * @return string[]
     */
    protected function clearedPath(array $params): array
    {
        $path = [];
        if (isset($params['path']) && is_array($params['path'])) {
            $path = array_map('strval', $params['path']);
        }
        if (isset($params['source']) && is_array($params['source'])) {
            $path = array_map('strval', $params['source']);
        }
        return array_values($path);
    }
}

<?php

namespace kalanis\kw_auth_sources\Access;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Hashes;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Statuses;
use kalanis\kw_auth_sources\Sources;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_files\Access\CompositeAdapter;
use kalanis\kw_locks\LockException;
use kalanis\kw_locks\Methods as lock_methods;
use kalanis\kw_storage\Interfaces\IStorage;


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
     * @param object|string|array<string|int, object|string> $params
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
            if ($params instanceof IStorage) {
                $storage = new Sources\Files\Storages\Storage($params, $this->getAusLang());
                $lock = new lock_methods\StorageLock($params);
                $accounts = new Sources\Files\AccountsSingleFile(
                    $storage,
                    new Hashes\CoreLib(),
                    new Statuses\Always(),
                    $lock,
                    [],
                    $this->getAusLang()
                );
                return new CompositeSources(
                    $accounts,
                    $accounts,
                    new Sources\Files\Groups($storage, $accounts, $lock, [], $this->getAusLang()),
                    new Sources\Classes()
                );
            }
            if ($params instanceof CompositeAdapter) {
                $storage = new Sources\Files\Storages\Files($params, $this->getAusLang());
                $lock = new lock_methods\FilesLock($params);
                $accounts = new Sources\Files\AccountsSingleFile(
                    $storage,
                    new Hashes\CoreLib(),
                    new Statuses\Always(),
                    $lock,
                    [],
                    $this->getAusLang()
                );
                return new CompositeSources(
                    $accounts,
                    $accounts,
                    new Sources\Files\Groups($storage, $accounts, $lock, [], $this->getAusLang()),
                    new Sources\Classes()
                );
            }
        } elseif (is_string($params)) {
            if ('ldap' == $params) {
                return new CompositeSources(
                    new Sources\Mapper\AuthLdap(),
                    new Sources\Dummy\Accounts(),
                    new Sources\Dummy\Groups(),
                    new Sources\Classes()
                );
            }
            if ('db' == $params) {
                $auth = new Sources\Mapper\AccountsDatabase(new Hashes\CoreLib());
                return new CompositeSources(
                    $auth,
                    $auth,
                    new Sources\Mapper\GroupsDatabase(),
                    new Sources\Classes()
                );
            }
            if (($dir = realpath($params)) && is_dir($params)) {
                $storage = new Sources\Files\Storages\Volume($dir . DIRECTORY_SEPARATOR, $this->getAusLang());
                $lock = new lock_methods\FileLock($dir . DIRECTORY_SEPARATOR . 'sources.lock');
                $accounts = new Sources\Files\AccountsSingleFile(
                    $storage,
                    new Hashes\CoreLib(),
                    new Statuses\Always(),
                    $lock,
                    [],
                    $this->getAusLang()
                );
                return new CompositeSources(
                    $accounts,
                    $accounts,
                    new Sources\Files\Groups($storage, $accounts, $lock, [], $this->getAusLang()),
                    new Sources\Classes()
                );
            }
        } elseif (is_array($params)) {
            // now it became a bit complicated...
        }
        throw new AuthSourcesException($this->getAusLang()->kauCombinationUnavailable());
    }
}

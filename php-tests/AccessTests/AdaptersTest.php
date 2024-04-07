<?php

namespace AccessTests;


use CommonTestClass;
use kalanis\kw_accounts\AccountsException;
use kalanis\kw_auth_sources\Access;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\ExtraParsers\Serialize;
use kalanis\kw_auth_sources\Hashes\Md5;
use kalanis\kw_auth_sources\Sources;
use kalanis\kw_auth_sources\Statuses\Always;
use kalanis\kw_locks\Methods\StorageLock;
use kalanis\kw_storage\Access as kw_store;
use kalanis\kw_storage\StorageException;


class AdaptersTest extends CommonTestClass
{
    public function testDirect(): void
    {
        $acc = new Sources\Dummy\Accounts();
        $lib = new Access\SourcesAdapters\Direct($acc, $acc, new Sources\Dummy\Groups(), new Sources\Classes());
        $this->assertInstanceOf(Sources\Dummy\Accounts::class, $lib->getAuth());
        $this->assertInstanceOf(Sources\Dummy\Accounts::class, $lib->getAccounts());
        $this->assertInstanceOf(Sources\Dummy\Groups::class, $lib->getGroups());
        $this->assertInstanceOf(Sources\Classes::class, $lib->getClasses());
    }

    /**
     * @throws AuthSourcesException
     */
    public function testNotSetAuth(): void
    {
        $lib = new XAccessAdapter();
        $this->expectException(AuthSourcesException::class);
        $lib->getAuth();
    }

    /**
     * @throws AuthSourcesException
     */
    public function testNotSetAccounts(): void
    {
        $lib = new XAccessAdapter();
        $this->expectException(AuthSourcesException::class);
        $lib->getAccounts();
    }

    /**
     * @throws AuthSourcesException
     */
    public function testNotSetGroups(): void
    {
        $lib = new XAccessAdapter();
        $this->expectException(AuthSourcesException::class);
        $lib->getGroups();
    }

    /**
     * @throws AuthSourcesException
     */
    public function testNotSetClasses(): void
    {
        $lib = new XAccessAdapter();
        $this->expectException(AuthSourcesException::class);
        $lib->getClasses();
    }

    /**
     * @throws AccountsException
     * @throws StorageException
     * @throws AuthSourcesException
     */
    public function testFirstInstance(): void
    {
        $storage = (new kw_store\Factory())->getStorage(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data');
        $fileStorage = new Sources\Files\Storages\Storage($storage);
        $fileParser = new Serialize();
        $fileLock = new StorageLock($storage);
        $fileAccounts = new Sources\Files\AccountsSingleFile(
            $fileStorage,
            new Md5(),
            new Always(),
            $fileParser,
            $fileLock,
            ['extra']
        );
        $lib = new Access\SourcesAdapters\FirstInstance(
            new Sources\Dummy\Accounts(),
            new Sources\Dummy\Groups(),
            new Sources\Classes(),
            new Sources\Files\Groups(
                $fileStorage,
                $fileAccounts,
                $fileParser,
                $fileLock,
                ['extra']
            ),
            $fileAccounts,
            'something else',
            123456
        );

        $this->assertInstanceOf(Sources\Dummy\Accounts::class, $lib->getAuth());
        $this->assertInstanceOf(Sources\Dummy\Accounts::class, $lib->getAccounts());
        $this->assertInstanceOf(Sources\Dummy\Groups::class, $lib->getGroups());
        $this->assertInstanceOf(Sources\Classes::class, $lib->getClasses());
    }

    /**
     * @throws AccountsException
     * @throws StorageException
     */
    public function testFirstInstanceNotEnough(): void
    {
        $storage = (new kw_store\Factory())->getStorage(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data');
        $fileStorage = new Sources\Files\Storages\Storage($storage);
        $fileParser = new Serialize();
        $fileLock = new StorageLock($storage);
        $fileAccounts = new Sources\Files\AccountsSingleFile(
            $fileStorage,
            new Md5(),
            new Always(),
            $fileParser,
            $fileLock,
            ['extra']
        );
        $this->expectException(AccountsException::class);
        new Access\SourcesAdapters\FirstInstance(
            new Sources\Dummy\Accounts(),
            new Sources\Dummy\Groups(),
            new Sources\Files\Groups(
                $fileStorage,
                $fileAccounts,
                $fileParser,
                $fileLock,
                ['extra']
            ),
            $fileAccounts,
            'something else',
            123456
        );
    }

    /**
     * @throws AccountsException
     * @throws StorageException
     */
    public function testLastInstance(): void
    {
        $storage = (new kw_store\Factory())->getStorage(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data');
        $fileStorage = new Sources\Files\Storages\Storage($storage);
        $fileParser = new Serialize();
        $fileLock = new StorageLock($storage);
        $fileAccounts = new Sources\Files\AccountsSingleFile(
            $fileStorage,
            new Md5(),
            new Always(),
            $fileParser,
            $fileLock,
            ['extra']
        );
        $lib = new Access\SourcesAdapters\LastInstance(
            new Sources\Dummy\Accounts(),
            new Sources\Dummy\Groups(),
            new Sources\Classes(),
            new Sources\Files\Groups(
                $fileStorage,
                $fileAccounts,
                $fileParser,
                $fileLock,
                ['extra']
            ),
            $fileAccounts,
            'something else',
            123456
        );

        $this->assertInstanceOf(Sources\Files\AccountsSingleFile::class, $lib->getAuth());
        $this->assertInstanceOf(Sources\Files\AccountsSingleFile::class, $lib->getAccounts());
        $this->assertInstanceOf(Sources\Files\Groups::class, $lib->getGroups());
        $this->assertInstanceOf(Sources\Classes::class, $lib->getClasses());
    }

    /**
     * @throws AccountsException
     * @throws StorageException
     */
    public function testLastInstanceNotEnough(): void
    {
        $storage = (new kw_store\Factory())->getStorage(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data');
        $fileStorage = new Sources\Files\Storages\Storage($storage);
        $fileParser = new Serialize();
        $fileLock = new StorageLock($storage);
        $fileAccounts = new Sources\Files\AccountsSingleFile(
            $fileStorage,
            new Md5(),
            new Always(),
            $fileParser,
            $fileLock,
            ['extra']
        );
        $this->expectException(AccountsException::class);
        new Access\SourcesAdapters\LastInstance(
            new Sources\Dummy\Accounts(),
            new Sources\Dummy\Groups(),
            new Sources\Files\Groups(
                $fileStorage,
                $fileAccounts,
                $fileParser,
                $fileLock,
                ['extra']
            ),
            $fileAccounts,
            'something else',
            123456
        );
    }
}


class XAccessAdapter extends Access\SourcesAdapters\AAdapter
{}

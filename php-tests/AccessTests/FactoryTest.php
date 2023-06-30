<?php

namespace AccessTests;


use CommonTestClass;
use kalanis\kw_auth_sources\Access\CompositeSources;
use kalanis\kw_auth_sources\Access\Factory;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Interfaces\IUser;
use kalanis\kw_auth_sources\Sources\Classes;
use kalanis\kw_auth_sources\Sources\Files\Storages\AStorage;
use kalanis\kw_auth_sources\Statuses\Always;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_files\Access\Factory as files_factory;
use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_locks\Interfaces\IKLTranslations;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;
use kalanis\kw_locks\Methods as lock_methods;
use kalanis\kw_paths\PathsException;
use kalanis\kw_storage\Storage\Key\DefaultKey;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target\Memory;


class FactoryTest extends CommonTestClass
{
    /**
     * @param $param
     * @throws AuthSourcesException
     * @throws LockException
     * @dataProvider passProvider
     */
    public function testPass($param): void
    {
        $lang = null;
        if (is_array($param) && isset($param['xlang']) && ($param['xlang'] instanceof Interfaces\IKAusTranslations)) {
            $lang = $param['xlang'];
        }
        $lib = new Factory($lang);
        $this->assertInstanceOf(CompositeSources::class, $lib->getSources($param));
    }

    /**
     * @throws AuthSourcesException
     * @throws FilesException
     * @throws LockException
     * @throws PathsException
     * @return array
     */
    public function passProvider(): array
    {
        $storage = new Storage(new DefaultKey(), new Memory());
        return [
            [(new Factory())->getSources('db')],
            ['ldap'],
            ['db'],
            [(new files_factory())->getClass(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data')],
            [__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data'],
            [['path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data']],

            [['source' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data']],
            [$storage],
            [['storage' => __DIR__ . DIRECTORY_SEPARATOR . '..', 'status' => 'sometimes', 'source' => ['data']]], // system directory with sub-dir, not every status
            [['storage' => new XLockedStorage(), 'status' => 1]], // combined storage and lock, no check limited for parts
            [['storage' => new \XFailedStorage(), 'status' => new Always()]], // IStorage, object status check

            [['storage' => (new files_factory())->getClass(__DIR__ . DIRECTORY_SEPARATOR . '..'), 'path' => ['data'], 'status' => 1, 'storage_lang' => new XFlLang()]], // CompositeAdapter, object status check
            [['storage' => (new files_factory())->getClass(__DIR__ . DIRECTORY_SEPARATOR . '..'), 'source' => ['data'], 'status' => false, 'xlang' => new XAuLang()]], // CompositeAdapter, object status check
            [['storage' => new \XFailedStorage(), 'hash' => new \MockHashes(), 'status' => new Always()]], // IStorage, object status check, hash object
            [['storage' => new \XFailedStorage(), 'hash' => 'yxcvbnmasdfghjklqwertzuiop9876543210', 'status' => new Always()]], // IStorage, object status check, hash with one string
            [['storage' => new \XFailedStorage(), 'status' => true, 'lock' => new lock_methods\StorageLock(new \XFailedStorage()), 'single_file' => true]], // IStorage, bool status check, lock defined, only single file with accounts

            [['storage' => new \XFailedStorage(), 'status' => true, 'lock' => new \XFailedStorage(), 'lock_lang' => new XLockLang()]], // IStorage, bool status check, lock source, lock lang extra
            [['storage' => (new files_factory())->getClass(__DIR__ . DIRECTORY_SEPARATOR . '..'), 'status' => true, 'lock' => (new files_factory())->getClass(__DIR__ . DIRECTORY_SEPARATOR . '..')]], // CompositeAdapter, bool status check, lock source
            [['path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data', 'classes' => new Classes()]], // path as string, classes as outside class
            [['path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data', 'groups' => new XMockGroups()]], // path as string, groups as outside class
            [['path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data', 'accounts' => new XMockAccount()]], // path as string, account as outside class

            [['path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data', 'accounts' => new XMockAccount(), 'auth' => new XMockAuth()]], // path as string, account as outside class, auth as outside class
            [['path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data', 'accounts' => new XMockAccount(), 'single_file' => true]], // path as string, account as outside class, auth in single file
        ];
    }

    /**
     * @param mixed $param
     * @throws AuthSourcesException
     * @throws LockException
     * @dataProvider failProvider
     */
    public function testFail($param): void
    {
        $lib = new Factory();
        $this->expectException(AuthSourcesException::class);
        $lib->getSources($param);
    }

    public function failProvider(): array
    {
        return [
            [true],
            [false],
            [null],
            [new \stdClass()],
            ['somewhere'],
            [['what' => 'irrelevant']],

            [['path' => []]],
            [['path' => null]],
            [['path' => new \stdClass()]],
            [['source' => []]],
            [['source' => null]],

            [['source' => new \stdClass()]],
            [['storage' => null]], // failed storage
            [['storage' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data', 'status' => null]], // failed status
            [['storage' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data', 'status' => 1, 'lock' => 123]], // failed lock
        ];
    }
}


class XLockedStorage extends AStorage implements ILock
{
    use TLang;

    /** @var array<string, string> */
    protected $storage = [];
    /** @var bool */
    protected $lock = false;

    /**
     * @param array<string, string> $initialData
     * @param Interfaces\IKAusTranslations|null $lang
     */
    public function __construct(array $initialData = [], ?Interfaces\IKAusTranslations $lang = null)
    {
        $this->setAusLang($lang);
        $this->storage = $initialData;
    }

    protected function open(array $path): string
    {
        $pt = implode(DIRECTORY_SEPARATOR, $path);
        if (!isset($this->storage[$pt])) {
            throw new AuthSourcesException($this->getAusLang()->kauPassFileNotFound($pt));
        }
        return strval($this->storage[$pt]);
    }

    protected function save(array $path, string $data): bool
    {
        $pt = implode(DIRECTORY_SEPARATOR, $path);
        $this->storage[$pt] = $data;
        return true;
    }

    public function has(): bool
    {
        return $this->lock;
    }

    public function create(bool $force = false): bool
    {
        $this->lock = true;
        return true;
    }

    public function delete(bool $force = false): bool
    {
        $this->lock = false;
        return true;
    }
}


class XFlLang implements IFLTranslations
{
    public function flCannotProcessNode(string $name): string
    {
        return 'mock';
    }

    public function flCannotLoadFile(string $fileName): string
    {
        return 'mock';
    }

    public function flCannotSaveFile(string $fileName): string
    {
        return 'mock';
    }

    public function flCannotOpenFile(string $fileName): string
    {
        return 'mock';
    }

    public function flCannotWriteFile(string $fileName): string
    {
        return 'mock';
    }

    public function flCannotGetFilePart(string $fileName): string
    {
        return 'mock';
    }

    public function flCannotGetSize(string $fileName): string
    {
        return 'mock';
    }

    public function flCannotCopyFile(string $sourceFileName, string $destFileName): string
    {
        return 'mock';
    }

    public function flCannotMoveFile(string $sourceFileName, string $destFileName): string
    {
        return 'mock';
    }

    public function flCannotRemoveFile(string $fileName): string
    {
        return 'mock';
    }

    public function flCannotCreateDir(string $dirName): string
    {
        return 'mock';
    }

    public function flCannotReadDir(string $dirName): string
    {
        return 'mock';
    }

    public function flCannotCopyDir(string $sourceDirName, string $destDirName): string
    {
        return 'mock';
    }

    public function flCannotMoveDir(string $sourceDirName, string $destDirName): string
    {
        return 'mock';
    }

    public function flCannotRemoveDir(string $dirName): string
    {
        return 'mock';
    }

    public function flNoDirectoryDelimiterSet(): string
    {
        return 'mock';
    }

    public function flNoProcessNodeSet(): string
    {
        return 'mock';
    }

    public function flNoProcessFileSet(): string
    {
        return 'mock';
    }

    public function flNoProcessDirSet(): string
    {
        return 'mock';
    }

    public function flNoAvailableClasses(): string
    {
        return 'mock';
    }
}


class XLockLang implements IKLTranslations
{
    public function iklLockedByOther(): string
    {
        return 'mock';
    }

    public function iklProblemWithStorage(): string
    {
        return 'mock';
    }

    public function iklCannotUseFile(string $lockFilename): string
    {
        return 'mock';
    }

    public function iklCannotUsePath(string $path): string
    {
        return 'mock';
    }

    public function iklCannotOpenFile(string $lockFilename): string
    {
        return 'mock';
    }

    public function iklCannotUseOS(): string
    {
        return 'mock';
    }
}


class XAuLang extends XFlLang implements Interfaces\IKAusTranslations, IKLTranslations
{
    public function kauPassFileNotFound(string $path): string
    {
        return 'mock';
    }

    public function kauPassMustBeSet(): string
    {
        return 'mock';
    }

    public function kauPassMissParam(): string
    {
        return 'mock';
    }

    public function kauPassLoginExists(): string
    {
        return 'mock';
    }

    public function kauLockSystemNotSet(): string
    {
        return 'mock';
    }

    public function kauAuthAlreadyOpen(): string
    {
        return 'mock';
    }

    public function kauGroupMissParam(): string
    {
        return 'mock';
    }

    public function kauGroupHasMembers(): string
    {
        return 'mock';
    }

    public function kauHashFunctionNotFound(): string
    {
        return 'mock';
    }

    public function kauCombinationUnavailable(): string
    {
        return 'mock';
    }

    public function iklLockedByOther(): string
    {
        return 'mock';
    }

    public function iklProblemWithStorage(): string
    {
        return 'mock';
    }

    public function iklCannotUseFile(string $lockFilename): string
    {
        return 'mock';
    }

    public function iklCannotUsePath(string $path): string
    {
        return 'mock';
    }

    public function iklCannotOpenFile(string $lockFilename): string
    {
        return 'mock';
    }

    public function iklCannotUseOS(): string
    {
        return 'mock';
    }
}


class XMockGroups implements Interfaces\IWorkGroups
{
    public function createGroup(Interfaces\IGroup $group): bool
    {
        return false;
    }

    public function getGroupDataOnly(string $groupId): ?Interfaces\IGroup
    {
        return null;
    }

    public function readGroup(): array
    {
        return [];
    }

    public function updateGroup(Interfaces\IGroup $group): bool
    {
        return false;
    }

    public function deleteGroup(string $groupId): bool
    {
        return false;
    }
}


class XMockAccount implements Interfaces\IWorkAccounts
{
    public function createAccount(IUser $user, string $password): bool
    {
        return false;
    }

    public function readAccounts(): array
    {
        return [];
    }

    public function updateAccount(IUser $user): bool
    {
        return false;
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        return false;
    }

    public function deleteAccount(string $userName): bool
    {
        return false;
    }
}


class XMockAuth implements Interfaces\IAuth
{
    public function getDataOnly(string $userName): ?IUser
    {
        return null;
    }

    public function authenticate(string $userName, array $params = []): ?IUser
    {
        return null;
    }
}

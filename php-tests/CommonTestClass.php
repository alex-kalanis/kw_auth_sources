<?php

use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_locks\LockException;
use kalanis\kw_locks\Methods as LockMethod;
use kalanis\kw_locks\Interfaces as LockInt;
use kalanis\kw_mapper\Interfaces\IDriverSources;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\StorageException;
use PHPUnit\Framework\TestCase;


\kalanis\kw_mapper\Storage\Database\ConfigStorage::getInstance()->addConfig(
    \kalanis\kw_mapper\Storage\Database\Config::init()->setTarget(
        IDriverSources::TYPE_RAW_MYSQLI, // no LDAP extension necessary for run
        'ldap',
        'localhost',
        1234567,
        null,
        null,
        ''
    )
);
\kalanis\kw_mapper\Storage\Database\ConfigStorage::getInstance()->addConfig(
    \kalanis\kw_mapper\Storage\Database\Config::init()->setTarget(
        IDriverSources::TYPE_RAW_MYSQLI, // need no real database connection
        'default_database',
        'localhost',
        1234567,
        null,
        null,
        ''
    )
);


/**
 * Class CommonTestClass
 * The structure for mocking and configuration seems so complicated, but it's necessary to let it be totally idiot-proof
 */
class CommonTestClass extends TestCase
{
    /**
     * @throws LockException
     * @return LockMethod\FileLock
     */
    protected function getLockPath(): LockMethod\FileLock
    {
        return new LockMethod\FileLock(
            __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . LockInt\ILock::LOCK_FILE
        );
    }
}


class MockUser implements Interfaces\IUser
{
    public function setUserData(?string $authId, ?string $authName, ?string $authGroup, ?int $authClass, ?int $authStatus, ?string $displayName, ?string $dir, ?array $extra = []): void
    {
    }

    public function getAuthId(): string
    {
        return '654';
    }

    public function getAuthName(): string
    {
        return 'fool';
    }

    public function getGroup(): string
    {
        return '456789';
    }

    public function getClass(): int
    {
        return 999;
    }

    public function getStatus(): int
    {
        return static::USER_STATUS_ENABLED;
    }

    public function getDisplayName(): string
    {
        return 'FooL';
    }

    public function getDir(): string
    {
        return 'not_available\\:///';
    }

    public function getExtra(): array
    {
        return [];
    }
}


class MockGroup implements Interfaces\IGroup
{
    public function setGroupData(?string $id, ?string $name, ?string $desc, ?string $authorId, ?int $status, ?array $parents = [], ?array $extra = []): void
    {
    }

    public function getGroupId(): string
    {
        return 'bazbazbaz';
    }

    public function getGroupName(): string
    {
        return 'FOO';
    }

    public function getGroupDesc(): string
    {
        return 'bar';
    }

    public function getGroupAuthorId(): string
    {
        return '123456789';
    }

    public function getGroupStatus(): int
    {
        return 999;
    }

    public function getGroupParents(): array
    {
        return [];
    }

    public function getGroupExtra(): array
    {
        return [];
    }
}


class MockHashes implements Interfaces\IHashes
{
    protected $knownPass = '';

    public function checkHash(string $pass, string $hash): bool
    {
        return ($this->knownPass == $pass) || ('valid' == $pass);
    }

    public function createHash(string $pass, ?string $method = null): string
    {
        $this->knownPass = $pass;
        return 'validPass-' . $pass;
    }
}


class XFailedStorage implements IStorage
{
    protected $canOpen = false;
    protected $content = '';

    public function __construct(bool $canOpen = false, string $content = '')
    {
        $this->canOpen = $canOpen;
        $this->content = $content;
    }

    public function canUse(): bool
    {
        return false;
    }

    public function isFlat(): bool
    {
        return false;
    }

    public function write(string $sharedKey, $data, ?int $timeout = null): bool
    {
        throw new StorageException('Mock');
    }

    public function read(string $sharedKey)
    {
        if ($this->canOpen) {
            return $this->content;
        }
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function remove(string $sharedKey): bool
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function exists(string $sharedKey): bool
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function lookup(string $mask): Traversable
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function increment(string $key): bool
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function decrement(string $key): bool
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }

    public function removeMulti(array $keys): array
    {
        throw new \kalanis\kw_storage\StorageException('Mock');
    }
}

<?php

namespace TraitsTests;


use CommonTestClass;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Traits;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;
use kalanis\kw_paths\PathsException;


class FilesTest extends CommonTestClass
{
    /**
     * @param string $in
     * @param string $want
     * @dataProvider stripProvider
     */
    public function testStrip(string $in, string $want): void
    {
        $lib = new MockLines();
        $this->assertEquals($want, $lib->stripChars($in));
    }

    public function stripProvider(): array
    {
        return [
            ['yxcvbnm', 'yxcvbnm'],
            ['jk~l,.qđwĐe', 'jkl,.qwe'],
        ];
    }

    /**
     * @param string $in
     * @param string $want
     * @throws PathsException
     * @dataProvider exImProvider
     */
    public function testExIm(string $in, string $want): void
    {
        $lib = new MockLines();
        $this->assertEquals($want, $lib->implosion($lib->explosion($in)));
    }

    public function exImProvider(): array
    {
        return [
            ['yxc:vb:nm', 'yxc:vb:nm'],
            ['yxcvbnm', 'yxcvbnm'],
            ['j°k~l:,.qđwĐe', 'j°k~l:,.qđwĐe'],
        ];
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testLockEmpty(): void
    {
        $lib = new MockAuthLock(null);
        $this->expectException(AuthSourcesException::class);
        $lib->check();
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testLockSimple(): void
    {
        $lib = new MockAuthLock($this->getLockPath());
        $lib->check();
        $this->assertTrue(true); // it runs, no errors
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testLockMix(): void
    {
        $lock = $this->getLockPath();
        $lib = new MockAuthLock($lock);
        $lock->create();
        $this->expectException(AuthSourcesException::class);
        $lib->check();
    }
}


class MockLines
{
    use Traits\TLines;

    protected function noDirectoryDelimiterSet(): string
    {
        return 'mock';
    }
}


class MockAuthLock
{
    use Traits\TAuthLock;

    public function __construct(?ILock $lock)
    {
        $this->initAuthLock($lock);
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function check(): void
    {
        $this->checkLock();
    }
}

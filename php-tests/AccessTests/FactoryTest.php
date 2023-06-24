<?php

namespace AccessTests;


use CommonTestClass;
use kalanis\kw_auth_sources\Access\CompositeSources;
use kalanis\kw_auth_sources\Access\Factory;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_files\Access\Factory as files_factory;
use kalanis\kw_files\FilesException;
use kalanis\kw_locks\LockException;
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
        $lib = new Factory();
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
//            [['path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data']],
//            [['source' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data']],
            [$storage],
//            [['source' => $storage]],
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
        ];
    }
}

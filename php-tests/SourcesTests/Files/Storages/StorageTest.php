<?php

namespace SourcesTests\Files\Volume;


use CommonTestClass;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Sources;
use kalanis\kw_files\Interfaces\IProcessNodes;
use kalanis\kw_storage\Storage\Key;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target;


class StorageTest extends CommonTestClass
{
    protected $sourcePath = [];
    protected $testingPath = [];

    protected function setUp(): void
    {
        $this->sourcePath = ['data', 'something.data'];
        $this->testingPath = ['.something.data-duplicate'];
    }

    /**
     * @throws AuthSourcesException
     */
    public function testSimple(): void
    {
        $lib = $this->getLib();
        $content = $lib->read($this->sourcePath);
        $this->assertNotEmpty($content);
        $this->assertTrue($lib->write($this->testingPath, $content));
    }

    /**
     * @throws AuthSourcesException
     */
    public function testReadCrash(): void
    {
        $lib = $this->getFailedLib();
        $this->expectException(AuthSourcesException::class);
        $lib->read($this->testingPath);
    }

    /**
     * @throws AuthSourcesException
     */
    public function testWriteCrash(): void
    {
        $lib = $this->getFailedLib();
        $this->expectException(AuthSourcesException::class);
        $lib->write($this->testingPath, []);
    }

    protected function getLib(): Sources\Files\Storages\Storage
    {
        $storage = new Storage(new Key\DefaultKey(), new Target\Memory());
        $storage->write('data', IProcessNodes::STORAGE_NODE_KEY);
        $storage->write('data' . DIRECTORY_SEPARATOR . 'something.data',
            'owner:1000:0:1:1:Owner:/data/:' . "\r\n"
            . 'manager:1001:1:2:1:Manage:/data/:' . "\r\n"
            . '# commented out' . "\r\n"
            . 'worker:1002:1:3:1:Worker:/data/:' . "\r\n"
        // last line is intentionally empty one
        );
        return new Sources\Files\Storages\Storage($storage);
    }

    protected function getFailedLib(): Sources\Files\Storages\Storage
    {
        return new Sources\Files\Storages\Storage(new \XFailedStorage());
    }
}



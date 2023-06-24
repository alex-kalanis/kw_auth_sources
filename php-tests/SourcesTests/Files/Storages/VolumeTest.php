<?php

namespace SourcesTests\Files\Volume;


use CommonTestClass;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Sources;


class VolumeTest extends CommonTestClass
{
    protected $sourcePath = [];
    protected $testingPath = [];

    protected function setUp(): void
    {
        $this->sourcePath = ['.groups'];
        $this->testingPath = ['.groups-duplicate'];
    }

    protected function tearDown(): void
    {
        $pt = implode(DIRECTORY_SEPARATOR, array_merge([$this->whereDir()], $this->testingPath));
        if (is_file($pt)) {
            unlink($pt);
        }
    }

    /**
     * @throws AuthSourcesException
     */
    public function testSimple(): void
    {
        $lib = new Sources\Files\Storages\Volume($this->whereDir());
        $content = $lib->read($this->sourcePath);
        $this->assertNotEmpty($content);
        $this->assertTrue($lib->write($this->testingPath, $content));
    }

    /**
     * @throws AuthSourcesException
     */
    public function testReadCrash(): void
    {
        $lib = new Sources\Files\Storages\Volume($this->whereDir());
        $this->expectException(AuthSourcesException::class);
        $lib->read($this->testingPath);
    }

    /**
     * @throws AuthSourcesException
     */
    public function testWriteCrash(): void
    {
        $lib = new Sources\Files\Storages\Volume($this->whereDir());
        $pt = implode(DIRECTORY_SEPARATOR, array_merge([$this->whereDir()], $this->testingPath));
        file_put_contents($pt, 'simple things');
        chmod($pt, 0444);
        $this->assertFalse($lib->write($this->testingPath, []));
    }

    protected function whereDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }
}

<?php

namespace SourcesTests\Files\Volume;


use CommonTestClass;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Sources\Files\Storages\Files;
use kalanis\kw_files\Access;
use kalanis\kw_files\FilesException;
use kalanis\kw_paths\PathsException;
use kalanis\kw_storage\Interfaces as storages_interfaces;
use kalanis\kw_storage\Storage\Key;
use kalanis\kw_storage\Storage\Storage;
use kalanis\kw_storage\Storage\Target;
use kalanis\kw_storage\StorageException;


class FilesTest extends CommonTestClass
{
    protected $sourcePath = ['', '.passcomb'];
    protected $testingPath = ['', 'other-file'];

    /**
     * @throws AuthSourcesException
     * @throws FilesException
     * @throws PathsException
     * @throws StorageException
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
     * @throws FilesException
     * @throws PathsException
     */
    public function testReadCrash(): void
    {
        $lib = $this->getFailedLib();
        $this->expectException(AuthSourcesException::class);
        $lib->read($this->testingPath);
    }

    /**
     * @throws AuthSourcesException
     * @throws FilesException
     * @throws PathsException
     */
    public function testWriteCrash(): void
    {
        $lib = $this->getFailedLib();
        $this->expectException(AuthSourcesException::class);
        $lib->write($this->testingPath, []);
    }

    /**
     * @throws FilesException
     * @throws PathsException
     * @throws StorageException
     * @return Files
     */
    protected function getLib(): Files
    {
        return new Files(
            (new Access\Factory())->getClass(new Storage(new Key\DefaultKey(), $this->filledMemorySingleFile()))
        );
    }

    /**
     * @throws StorageException
     * @return storages_interfaces\ITarget
     */
    protected function filledMemorySingleFile(): storages_interfaces\ITarget
    {
        $lib = new Target\Memory();
        $lib->save(
            DIRECTORY_SEPARATOR . '.passcomb',
            '1000:owner:$2y$10$6-bucFamnK5BTGbojaWw3!HzzHOlUNnN6PF3Y9qHQIdE8FmQKv/eq:0:1:1:Owner:/data/::' . "\r\n"
            . '1001:manager:$2y$10$G1Fo0udxqekABHkzUQubfuD8AjgD/5O9F9v3E0qYG2TI0BfZAkyz2:1:2:1:Manage:/data/::' . "\r\n"
            . '# commented out' . "\r\n"
            . '1002:worker:$2y$10$6.bucFamnK5BTGbojaWw3.HpzHOlQUnN6PF3Y9qHQIdE8FmQKv/eq:1:3:1:Worker:/data/::' . "\r\n"
        // last line is intentionally empty one
        );
        return $lib;
    }

    /**
     * @throws FilesException
     * @throws PathsException
     * @return Files
     */
    protected function emptyFileSources(): Files
    {
        return new Files(
            (new Access\Factory())->getClass(new Storage(new Key\DefaultKey(), new Target\Memory()))
        );
    }

    /**
     * @param bool $canOpen
     * @param string $content
     * @throws FilesException
     * @throws PathsException
     * @return Files
     */
    protected function getFailedLib(bool $canOpen = false, string $content = ''): Files
    {
        return new Files(
            (new Access\Factory())->getClass(new \XFailedStorage($canOpen, $content))
        );
    }
}

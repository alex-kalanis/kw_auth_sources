<?php

namespace SourcesTests\Files;


use CommonTestClass;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\ExtraParsers;
use kalanis\kw_auth_sources\Interfaces\IFile;
use kalanis\kw_auth_sources\Interfaces\IKAusTranslations;
use kalanis\kw_auth_sources\Sources\Files;
use kalanis\kw_auth_sources\Statuses\Always;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;
use MockHashes;


abstract class AFilesTest extends CommonTestClass
{
    protected $sourcePath = ['data'];

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws LockException
     * @return Files\Groups
     */
    protected function fullGroupsSources(): Files\Groups
    {
        return new Files\Groups(
            $this->getStoragesFullFiles(),
            $this->fullFilesSources(),
            new ExtraParsers\Json(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws LockException
     * @return Files\Groups
     */
    protected function lockedGroupsSources(): Files\Groups
    {
        return new Files\Groups(
            $this->getStoragesFullFiles(),
            $this->fullFilesSources(),
            new ExtraParsers\Json(),
            new XLockPermanent(),
            $this->sourcePath
        );
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws LockException
     * @return Files\AccountsMultiFile
     */
    protected function fullFilesSources(): Files\AccountsMultiFile
    {
        return new Files\AccountsMultiFile(
            $this->getStoragesFullFiles(),
            new MockHashes(),
            new Always(),
            new ExtraParsers\Json(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @return Files\AccountsMultiFile
     */
    protected function lockFailFilesSources(): Files\AccountsMultiFile
    {
        return new Files\AccountsMultiFile(
            $this->getStoragesFullFiles(),
            new MockHashes(),
            new Always(),
            new ExtraParsers\Json(),
            new XLockPermanent(),
            $this->sourcePath
        );
    }

    protected function getStoragesFullFiles(): XStorage
    {
        return new XStorage([
            'data' . DIRECTORY_SEPARATOR . IFile::PASS_FILE
            => 'owner:1000:0:1:1:Owner:/data/::' . "\r\n"
                . 'manager:1001:1:2:1:Manage:/data/:{"hint"\:"Uncut","age"\:39,"powers"\:["foo","bar","baz"]}:' . "\r\n"
                . '# commented out' . "\r\n"
                . 'worker:1002:1:3:1:Worker:/data/::' . "\r\n"
            // last line is intentionally empty one
            ,
            'data' . DIRECTORY_SEPARATOR . IFile::SHADE_FILE
            => 'owner:M2FjMjZhMjc3MGY4MzUxYjYyN2YzMzI1NjRkNTVlYmM4N2U5N2Y3ODI2NDAwMjY0MTZmMTI0NTliOTFlMTUxZQ==:0:9999999999:7:x:' . "\r\n"
                . 'manager:ZWZmNzQwODIxZDhjNzRkMjZlZTIzYjQ2ODBiNDA1YTA5MWY0ZjdkNWVhNzk2NDAxZTZkODY3NDhmMjg0MzE4Yw==:0:9999999999:salt_hash:x:' . "\r\n"
                . '# commented out' . "\r\n"
                . 'worker:M2FjMjZhMjc3MGY4MzUxYjYyN2YzMzI1NjRkNTVlYmM4N2U5N2Y3ODI2NDAwMjY0MTZmMTI0NTliOTFlMTUxZQ==:0:9999999999:salt_key:x:' . "\r\n"
            // last line is intentionally empty one
            ,
            'data' . DIRECTORY_SEPARATOR . IFile::GROUP_FILE
            => '0:root:1000:Maintainers:1::' . "\r\n"
                . '1:admin:1000:Administrators:1:{"hint"\:"Sssh","age"\:39,"powers"\:["foo","bar","baz"]}:' . "\r\n"
                . '# commented out' . "\r\n"
                . '2:user:1000:All users:1::' . "\r\n"
            // last line is intentionally empty one
        ]);
    }

    /**
     * Contains a partial files - no groups or shadow files
     * @throws LockException
     * @return Files\AccountsMultiFile
     */
    protected function partialFilesSources(): Files\AccountsMultiFile
    {
        return new Files\AccountsMultiFile(
            $this->getStoragesPartial(),
            new MockHashes(),
            new Always(),
            new ExtraParsers\Json(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    protected function getStoragesPartial(): XStorage
    {
        return new XStorage([
            'data' . DIRECTORY_SEPARATOR . IFile::PASS_FILE
            => 'owner:1000:0:1:1:Owner:/data/::' . "\r\n"
                . 'manager:1001:1:2:1:Manage:/data/:{"hint"\:"Uncut","age"\:39,"powers"\:["foo","bar","baz"]}:' . "\r\n"
                . '# commented out' . "\r\n"
                . 'worker:1002:1:3:1:Worker:/data/::' . "\r\n"
            // last line is intentionally empty one
        ]);
    }

    /**
     * Contains a partial files - no groups or shadow files
     * @throws LockException
     * @return Files\AccountsSingleFile
     */
    protected function partialFileSources(): Files\AccountsSingleFile
    {
        return new Files\AccountsSingleFile(
            $this->getStoragePartial(),
            new MockHashes(),
            new Always(),
            new ExtraParsers\Serialize(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    /**
     * Contains a partial files - no groups or shadow files
     * @return Files\AccountsSingleFile
     */
    protected function lockFailFileSources(): Files\AccountsSingleFile
    {
        return new Files\AccountsSingleFile(
            $this->getStoragePartial(),
            new MockHashes(),
            new Always(),
            new ExtraParsers\Serialize(),
            new XLockPermanent(),
            $this->sourcePath
        );
    }

    protected function getStoragePartial(): XStorage
    {
        return new XStorage([
            'data' . DIRECTORY_SEPARATOR . '.passcomb'
            => '1000:owner:$2y$10$6-bucFamnK5BTGbojaWw3!HzzHOlUNnN6PF3Y9qHQIdE8FmQKv/eq:0:1:1:Owner:/data/::' . "\r\n"
                . '1001:manager:$2y$10$G1Fo0udxqekABHkzUQubfuD8AjgD/5O9F9v3E0qYG2TI0BfZAkyz2:1:2:1:Manage:/data/:a\:3\:{s\:4\:"hint";s\:5\:"Uncut";s\:3\:"age";i\:39;s\:6\:"powers";a\:3\:{i\:0;s\:3\:"foo";i\:1;s\:3\:"bar";i\:2;s\:3\:"baz";}}:' . "\r\n"
                . '# commented out' . "\r\n"
                . '1002:worker:$2y$10$6.bucFamnK5BTGbojaWw3.HpzHOlQUnN6PF3Y9qHQIdE8FmQKv/eq:1:3:1:Worker:/data/::' . "\r\n"
            // last line is intentionally empty one
        ]);
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws LockException
     * @return Files\Groups
     */
    protected function emptyGroupSources(): Files\Groups
    {
        return new Files\Groups(
            new XStorage(),
            $this->emptyFilesSources(),
            new ExtraParsers\Serialize(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws LockException
     * @return Files\Groups
     */
    protected function emptyGroupSourcesWithFiles(): Files\Groups
    {
        return new Files\Groups(
            new XStorage(),
            $this->fullFilesSources(),
            new ExtraParsers\Serialize(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws LockException
     * @return Files\AccountsMultiFile
     */
    protected function emptyFilesSources(): Files\AccountsMultiFile
    {
        return new Files\AccountsMultiFile(
            new XStorage(),
            new MockHashes(),
            new Always(),
            new ExtraParsers\Serialize(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @throws LockException
     * @return Files\AccountsSingleFile
     */
    protected function emptyFileSources(): Files\AccountsSingleFile
    {
        return new Files\AccountsSingleFile(
            new XStorage(),
            new MockHashes(),
            new Always(),
            new ExtraParsers\Serialize(),
            $this->getLockPath(),
            $this->sourcePath
        );
    }
}


class XStorage extends Files\Storages\AStorage
{
    use TLang;

    /** @var array<string, string> */
    protected $storage = [];

    /**
     * @param array<string, string> $initialData
     * @param IKAusTranslations|null $lang
     */
    public function __construct(array $initialData = [], ?IKAusTranslations $lang = null)
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

    protected function noDirectoryDelimiterSet(): string
    {
        return 'mock';
    }
}


class XLockPermanent implements ILock
{
    public function has(): bool
    {
        return true;
    }

    public function create(bool $force = false): bool
    {
        return false;
    }

    public function delete(bool $force = false): bool
    {
        return false;
    }
}

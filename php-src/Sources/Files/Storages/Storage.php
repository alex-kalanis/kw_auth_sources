<?php

namespace kalanis\kw_auth_sources\Sources\Files\Storages;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces\IKAusTranslations;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_files\FilesException;
use kalanis\kw_files\Traits\TToString;
use kalanis\kw_paths\PathsException;
use kalanis\kw_paths\Stuff;
use kalanis\kw_storage\Interfaces\IStorage;
use kalanis\kw_storage\StorageException;


/**
 * Class Storage
 * @package kalanis\kw_auth_sources\Sources\Files\Storages
 * Processing files in storage defined with interfaces from kw_storage
 */
class Storage extends AStorage
{
    use TLang;
    use TToString;

    /** @var IStorage */
    protected $storage = null;

    public function __construct(IStorage $storage, ?IKAusTranslations $ausLang = null)
    {
        $this->storage = $storage;
        $this->setAusLang($ausLang);
    }

    protected function open(array $path): string
    {
        try {
            return $this->toString(Stuff::arrayToPath($path), $this->storage->read(Stuff::arrayToPath($path)));
        } catch (FilesException | StorageException | PathsException $ex) {
            throw new AuthSourcesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    protected function save(array $path, string $content): bool
    {
        try {
            return $this->storage->write(Stuff::arrayToPath($path), $content);
        } catch (StorageException | PathsException $ex) {
            throw new AuthSourcesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @return string
     * @codeCoverageIgnore translation
     */
    protected function noDirectoryDelimiterSet(): string
    {
        return $this->getAusLang()->kauNoDelimiterSet();
    }
}

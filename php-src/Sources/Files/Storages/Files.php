<?php

namespace kalanis\kw_auth_sources\Sources\Files\Storages;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces\IKAusTranslations;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_files\FilesException;
use kalanis\kw_files\Interfaces\IFLTranslations;
use kalanis\kw_files\Interfaces\IProcessFiles;
use kalanis\kw_files\Traits\TToString;
use kalanis\kw_paths\PathsException;
use kalanis\kw_paths\Stuff;


/**
 * Class Files
 * @package kalanis\kw_auth_sources\Sources\Files\Storages
 * Processing files in storage defined with interfaces from kw_files
 */
class Files extends AStorage
{
    use TLang;
    use TToString;

    protected IProcessFiles $files;

    public function __construct(IProcessFiles $files, ?IKAusTranslations $ausLang = null, ?IFLTranslations $flLang = null)
    {
        $this->files = $files;
        $this->setAusLang($ausLang);
        $this->setFlLang($flLang); // TToString
    }

    protected function open(array $path): string
    {
        try {
            return $this->toString(Stuff::arrayToPath($path), $this->files->readFile($path));
        } catch (FilesException | PathsException $ex) {
            throw new AuthSourcesException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    protected function save(array $path, string $content): bool
    {
        try {
            return $this->files->saveFile($path, $content);
        } catch (FilesException | PathsException $ex) {
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

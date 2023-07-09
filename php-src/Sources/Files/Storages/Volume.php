<?php

namespace kalanis\kw_auth_sources\Sources\Files\Storages;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces\IKAusTranslations;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_paths\PathsException;
use kalanis\kw_paths\Stuff;


/**
 * Class Volume
 * @package kalanis\kw_auth_sources\Sources\Files\Volume
 * Processing files on local volume
 */
class Volume extends AStorage
{
    use TLang;

    /** @var string */
    protected $startDirectory = '';

    public function __construct(string $where, ?IKAusTranslations $ausLang = null)
    {
        $this->startDirectory = $where;
        $this->setAusLang($ausLang);
    }

    protected function open(array $path): string
    {
        try {
            $pt = $this->startDirectory . Stuff::arrayToPath($path);
            $content = @file_get_contents($pt);
            if (false === $content) {
                throw new AuthSourcesException($this->getAusLang()->kauPassFileNotFound($pt));
            }
            return strval($content);
            // @codeCoverageIgnoreStart
        } catch (PathsException $ex) {
            throw new AuthSourcesException($ex->getMessage(), $ex->getCode(), $ex);
        }
        // @codeCoverageIgnoreEnd
    }

    protected function save(array $path, string $content): bool
    {
        try {
            $pt = $this->startDirectory . Stuff::arrayToPath($path);
            return boolval(@file_put_contents($pt, $content));
            // @codeCoverageIgnoreStart
        } catch (PathsException $ex) {
            throw new AuthSourcesException($ex->getMessage(), $ex->getCode(), $ex);
        }
        // @codeCoverageIgnoreEnd
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

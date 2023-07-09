<?php

namespace kalanis\kw_auth_sources\Sources\Files\Storages;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces\IFile;
use kalanis\kw_auth_sources\Traits\TLines;


/**
 * Class AStorage
 * @package kalanis\kw_auth_sources\Sources\Files\Files
 * Storages of files with data for authentication
 */
abstract class AStorage
{
    use TLines;

    /**
     * @param string[] $path
     * @throws AuthSourcesException
     * @return array<int, array<int, string>>
     */
    public function read(array $path): array
    {
        $lines = explode(IFile::CRLF, $this->open($path));
        return array_map([$this, 'explosion'], array_filter(array_map('trim', $lines), [$this, 'filterEmptyLines']));
    }

    /**
     * @param string[] $path
     * @throws AuthSourcesException
     * @return string
     */
    abstract protected function open(array $path): string;

    /**
     * @param string[] $path
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthSourcesException
     * @return bool
     */
    public function write(array $path, array $lines): bool
    {
        return $this->save($path, strval(implode(IFile::CRLF, array_map([$this, 'implosion'], $lines)) . IFile::CRLF));
    }

    /**
     * @param string[] $path
     * @param string $data
     * @throws AuthSourcesException
     * @return bool
     */
    abstract protected function save(array $path, string $data): bool;
}

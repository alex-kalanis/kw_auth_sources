<?php

namespace kalanis\kw_auth_sources\Traits;


use kalanis\kw_auth_sources\Interfaces\IFile;
use kalanis\kw_paths\Extras\TPathTransform;
use kalanis\kw_paths\PathsException;


/**
 * Trait TLines
 * @package kalanis\kw_auth_sources\Traits
 * Processing lines of accounts in files
 */
trait TLines
{
    use TPathTransform;

    /**
     * @param string $input
     * @throws PathsException
     * @return array<int, string>
     */
    public function explosion(string $input): array
    {
        return $this->expandName($input,IFile::SEPARATOR);
    }

    /**
     * @param array<int, string|int|float> $input
     * @throws PathsException
     * @return string
     */
    public function implosion(array $input): string
    {
        return $this->compactName(array_map('strval', $input + ['']), IFile::SEPARATOR);
    }

    /**
     * @param string $input
     * @return bool
     */
    public function filterEmptyLines(string $input): bool
    {
        return !empty($input) && ('#' !== $input[0]);
    }

    public function stripChars(string $input): string
    {
        return strval(preg_replace('#[^a-zA-Z0-9\,\*\/\.\-\+\?\_\§\"\!\/\(\)\|\€\'\\\&\@\{\}\<\>\#\ ]#', '', $input));
    }
}

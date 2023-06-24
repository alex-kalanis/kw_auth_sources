<?php

namespace kalanis\kw_auth_sources\Sources;


use kalanis\kw_auth_sources\Interfaces\IFile;


/**
 * Trait TLines
 * @package kalanis\kw_auth_sources\Sources
 * Processing lines of accounts in files
 */
trait TLines
{
    /**
     * @param string $input
     * @return array<int, string>
     */
    public function explosion(string $input): array
    {
        return explode(IFile::SEPARATOR, $input);
    }

    /**
     * @param array<int, string|int|float> $input
     * @return string
     */
    public function implosion(array $input): string
    {
        return implode(IFile::SEPARATOR, $input + ['']);
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

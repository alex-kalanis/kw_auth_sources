<?php

namespace kalanis\kw_auth_sources\ExtraParsers;


use kalanis\kw_auth_sources\Interfaces\IExtraParser;


/**
 * Class None
 * @package kalanis\kw_auth_sources\ExtraParsers
 * No data thru
 */
class None implements IExtraParser
{
    public function compact(array $pass): string
    {
        return '';
    }

    public function expand(string $pass): array
    {
        return [];
    }
}

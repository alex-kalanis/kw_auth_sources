<?php

namespace kalanis\kw_auth_sources\ExtraParsers;


use kalanis\kw_auth_sources\Interfaces\IExtraParser;


/**
 * Class Serialize
 * @package kalanis\kw_auth_sources\ExtraParsers
 * Compact and expand params via serialization
 */
class Serialize implements IExtraParser
{
    public function compact(array $pass): string
    {
        return serialize($pass);
    }

    public function expand(string $pass): array
    {
        return (array) unserialize($pass);
    }
}

<?php

namespace kalanis\kw_auth_sources\ExtraParsers;


use kalanis\kw_auth_sources\Interfaces\IExtraParser;


/**
 * Class Json
 * @package kalanis\kw_auth_sources\ExtraParsers
 * Compact and expand params via json encode
 */
class Json implements IExtraParser
{
    public function compact(array $pass): string
    {
        return strval(json_encode($pass));
    }

    public function expand(string $pass): array
    {
        return (array) json_decode($pass, true);
    }
}

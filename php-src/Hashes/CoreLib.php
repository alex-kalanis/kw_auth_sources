<?php

namespace kalanis\kw_auth_sources\Hashes;


use kalanis\kw_auth_sources\Interfaces\IHashes;


/**
 * Class CoreLib
 * @package kalanis\kw_auth_sources\Hashes
 * Check hashes via core libraries
 */
class CoreLib implements IHashes
{
    public function checkHash(string $pass, string $hash): bool
    {
        return password_verify($pass, $hash);
    }

    public function createHash(string $pass, ?string $method = null): string
    {
        return strval(password_hash($pass, PASSWORD_DEFAULT));
    }
}

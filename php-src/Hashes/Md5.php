<?php

namespace kalanis\kw_auth_sources\Hashes;


use kalanis\kw_auth_sources\Interfaces\IHashes;


/**
 * Class Md5
 * @package kalanis\kw_auth_sources\Hashes
 * Hashes via Md5 checksum (UNSAFE!!!)
 */
class Md5 implements IHashes
{
    public function checkHash(string $pass, string $hash): bool
    {
        return md5($pass) == $hash;
    }

    public function createHash(string $pass, ?string $method = null): string
    {
        return md5($pass);
    }
}

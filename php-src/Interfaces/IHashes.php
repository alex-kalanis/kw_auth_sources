<?php

namespace kalanis\kw_auth_sources\Interfaces;


use kalanis\kw_auth_sources\AuthSourcesException;


/**
 * Interface IHashes
 * @package kalanis\kw_auth_sources\Interfaces
 * Hashes
 */
interface IHashes
{
    /**
     * Check itself
     * @param string $pass
     * @param string $hash
     * @throws AuthSourcesException
     * @return bool
     */
    public function checkHash(string $pass, string $hash): bool;

    /**
     * Create new one
     * @param string $pass
     * @param string|null $method
     * @throws AuthSourcesException
     * @return string
     */
    public function createHash(string $pass, ?string $method = null): string;
}

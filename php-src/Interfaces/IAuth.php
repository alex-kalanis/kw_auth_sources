<?php

namespace kalanis\kw_auth_sources\Interfaces;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_locks\LockException;


/**
 * Interface IAuth
 * @package kalanis\kw_auth_sources\Interfaces
 * Authentication sources available on your system
 */
interface IAuth
{
    /**
     * Get data about chosen user
     * @param string $userName
     * @throws AuthSourcesException
     * @throws LockException
     * @return IUser|null
     */
    public function getDataOnly(string $userName): ?IUser;

    /**
     * Check if credentials are okay
     * @param string $userName
     * @param array<string|int, string|int|float|bool> $params
     * @throws AuthSourcesException
     * @throws LockException
     * @return IUser|null
     */
    public function authenticate(string $userName, array $params = []): ?IUser;
}

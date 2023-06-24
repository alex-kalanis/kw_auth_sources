<?php

namespace kalanis\kw_auth_sources\Interfaces;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_locks\LockException;


/**
 * Interface IWorkAccounts
 * @package kalanis\kw_auth_sources\Interfaces
 * Work with accounts
 */
interface IWorkAccounts
{
    /**
     * @param IUser $user
     * @param string $password
     * @throws AuthSourcesException
     * @throws LockException
     * @return bool
     */
    public function createAccount(IUser $user, string $password): bool;

    /**
     * @throws AuthSourcesException
     * @throws LockException
     * @return IUser[]
     */
    public function readAccounts(): array;

    /**
     * @param IUser $user
     * @throws LockException
     * @throws AuthSourcesException
     * @return bool
     */
    public function updateAccount(IUser $user): bool;

    /**
     * @param string $userName
     * @param string $passWord
     * @throws AuthSourcesException
     * @throws LockException
     * @return bool
     */
    public function updatePassword(string $userName, string $passWord): bool;

    /**
     * @param string $userName
     * @throws AuthSourcesException
     * @throws LockException
     * @return bool
     */
    public function deleteAccount(string $userName): bool;
}

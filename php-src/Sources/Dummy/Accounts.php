<?php

namespace kalanis\kw_auth_sources\Sources\Dummy;


use kalanis\kw_accounts\Interfaces;


/**
 * Class Accounts
 * @package kalanis\kw_auth_sources\Sources\Dummy
 * Dummy Authenticate class
 */
class Accounts implements Interfaces\IAuth, Interfaces\IProcessAccounts
{
    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        return null;
    }

    public function getDataOnly(string $userName): ?Interfaces\IUser
    {
        return null;
    }

    public function createAccount(Interfaces\IUser $user, string $password): bool
    {
        return false;
    }

    public function readAccounts(): array
    {
        return [];
    }

    public function updateAccount(Interfaces\IUser $user): bool
    {
        return false;
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        return false;
    }

    public function deleteAccount(string $userName): bool
    {
        return false;
    }
}

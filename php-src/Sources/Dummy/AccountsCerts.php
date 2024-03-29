<?php

namespace kalanis\kw_auth_sources\Sources\Dummy;


use kalanis\kw_accounts\Interfaces;


/**
 * Class AccountsCerts
 * @package kalanis\kw_auth_sources\Sources\Dummy
 * Dummy Authenticate class with certificates
 */
class AccountsCerts extends Accounts implements Interfaces\IAuthCert
{
    public function updateCertData(string $userName, ?string $certKey, ?string $certSalt): bool
    {
        return false;
    }

    public function getCertData(string $userName): ?Interfaces\ICert
    {
        return null;
    }
}

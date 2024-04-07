<?php

namespace kalanis\kw_auth_sources\Sources\Memory;


use kalanis\kw_accounts\Interfaces;


/**
 * Class AccountsCerts
 * @package kalanis\kw_auth_sources\Sources\Memory
 * Authenticate class with certificates - in memory
 */
class AccountsCerts extends Accounts implements Interfaces\IAuthCert
{
    /** @var Interfaces\IUserCert[] */
    protected array $local = [];

    public function updateCertData(string $userName, ?string $certKey, ?string $certSalt): bool
    {
        foreach ($this->local as $item) {
            if ($item->getAuthName() == $userName) {
                $item->updateCertInfo($certKey, $certSalt);
                return true;
            }
        }
        return false;
    }

    public function getCertData(string $userName): ?Interfaces\ICert
    {
        $user = $this->getDataOnly($userName);
        return ($user && ($user instanceof Interfaces\IUserCert)) ? clone $user : null;
    }
}

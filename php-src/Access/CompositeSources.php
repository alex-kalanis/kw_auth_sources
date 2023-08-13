<?php

namespace kalanis\kw_auth_sources\Access;


use kalanis\kw_auth_sources\Interfaces;


/**
 * Class CompositeSources
 * @package kalanis\kw_auth_sources\Access
 */
class CompositeSources implements Interfaces\IAuthCert, Interfaces\IWorkAccounts, Interfaces\IWorkClasses, Interfaces\IWorkGroups
{
    /** @var SourcesAdapters\AAdapter */
    protected $adapter = null;

    public function __construct(SourcesAdapters\AAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getDataOnly(string $userName): ?Interfaces\IUser
    {
        return $this->adapter->getAuth()->getDataOnly($userName);
    }

    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        return $this->adapter->getAuth()->authenticate($userName, $params);
    }

    public function updateCertKeys(string $userName, ?string $certKey, ?string $certSalt): bool
    {
        $auth = $this->adapter->getAuth();
        if ($auth instanceof Interfaces\IAuthCert) {
            return $auth->updateCertKeys($userName, $certKey, $certSalt);
        }
        return false;
    }

    public function getCertData(string $userName): ?Interfaces\IUserCert
    {
        $auth = $this->adapter->getAuth();
        if ($auth instanceof Interfaces\IAuthCert) {
            return $auth->getCertData($userName);
        }
        return null;
    }

    public function createAccount(Interfaces\IUser $user, string $password): bool
    {
        return $this->adapter->getAccounts()->createAccount($user, $password);
    }

    public function readAccounts(): array
    {
        return $this->adapter->getAccounts()->readAccounts();
    }

    public function updateAccount(Interfaces\IUser $user): bool
    {
        return $this->adapter->getAccounts()->updateAccount($user);
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        return $this->adapter->getAccounts()->updatePassword($userName, $passWord);
    }

    public function deleteAccount(string $userName): bool
    {
        return $this->adapter->getAccounts()->deleteAccount($userName);
    }

    public function readClasses(): array
    {
        return $this->adapter->getClasses()->readClasses();
    }

    public function createGroup(Interfaces\IGroup $group): bool
    {
        return $this->adapter->getGroups()->createGroup($group);
    }

    public function getGroupDataOnly(string $groupId): ?Interfaces\IGroup
    {
        return $this->adapter->getGroups()->getGroupDataOnly($groupId);
    }

    public function readGroup(): array
    {
        return $this->adapter->getGroups()->readGroup();
    }

    public function updateGroup(Interfaces\IGroup $group): bool
    {
        return $this->adapter->getGroups()->updateGroup($group);
    }

    public function deleteGroup(string $groupId): bool
    {
        return $this->adapter->getGroups()->deleteGroup($groupId);
    }

    public function getAuth(): Interfaces\IAuth
    {
        return $this->adapter->getAuth();
    }

    public function getAccounts(): Interfaces\IWorkAccounts
    {
        return $this->adapter->getAccounts();
    }

    public function getClasses(): Interfaces\IWorkClasses
    {
        return $this->adapter->getClasses();
    }

    public function getGroups(): Interfaces\IWorkGroups
    {
        return $this->adapter->getGroups();
    }
}

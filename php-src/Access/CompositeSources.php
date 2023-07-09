<?php

namespace kalanis\kw_auth_sources\Access;


use kalanis\kw_auth_sources\Interfaces;


/**
 * Class CompositeSources
 * @package kalanis\kw_auth_sources\Access
 */
class CompositeSources implements Interfaces\IAuthCert, Interfaces\IWorkAccounts, Interfaces\IWorkClasses, Interfaces\IWorkGroups
{
    /** @var Interfaces\IAuth */
    protected $auth = null;
    /** @var Interfaces\IWorkAccounts */
    protected $accounts = null;
    /** @var Interfaces\IWorkClasses */
    protected $classes = null;
    /** @var Interfaces\IWorkGroups */
    protected $groups = null;

    public function __construct(Interfaces\IAuth $auth, Interfaces\IWorkAccounts $accounts, Interfaces\IWorkGroups $groups, Interfaces\IWorkClasses $classes)
    {
        $this->auth = $auth;
        $this->accounts = $accounts;
        $this->classes = $classes;
        $this->groups = $groups;
    }

    public function getDataOnly(string $userName): ?Interfaces\IUser
    {
        return $this->auth->getDataOnly($userName);
    }

    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        return $this->auth->authenticate($userName, $params);
    }

    public function updateCertKeys(string $userName, ?string $certKey, ?string $certSalt): bool
    {
        if ($this->auth instanceof Interfaces\IAuthCert) {
            return $this->auth->updateCertKeys($userName, $certKey, $certSalt);
        }
        return false;
    }

    public function getCertData(string $userName): ?Interfaces\IUserCert
    {
        if ($this->auth instanceof Interfaces\IAuthCert) {
            return $this->auth->getCertData($userName);
        }
        return null;
    }

    public function createAccount(Interfaces\IUser $user, string $password): bool
    {
        return $this->accounts->createAccount($user, $password);
    }

    public function readAccounts(): array
    {
        return $this->accounts->readAccounts();
    }

    public function updateAccount(Interfaces\IUser $user): bool
    {
        return $this->accounts->updateAccount($user);
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        return $this->accounts->updatePassword($userName, $passWord);
    }

    public function deleteAccount(string $userName): bool
    {
        return $this->accounts->deleteAccount($userName);
    }

    public function readClasses(): array
    {
        return $this->classes->readClasses();
    }

    public function createGroup(Interfaces\IGroup $group): bool
    {
        return $this->groups->createGroup($group);
    }

    public function getGroupDataOnly(string $groupId): ?Interfaces\IGroup
    {
        return $this->groups->getGroupDataOnly($groupId);
    }

    public function readGroup(): array
    {
        return $this->groups->readGroup();
    }

    public function updateGroup(Interfaces\IGroup $group): bool
    {
        return $this->groups->updateGroup($group);
    }

    public function deleteGroup(string $groupId): bool
    {
        return $this->groups->deleteGroup($groupId);
    }

    public function getAuth(): Interfaces\IAuth
    {
        return $this->auth;
    }

    public function getAccounts(): Interfaces\IWorkAccounts
    {
        return $this->accounts;
    }

    public function getClasses(): Interfaces\IWorkClasses
    {
        return $this->classes;
    }

    public function getGroups(): Interfaces\IWorkGroups
    {
        return $this->groups;
    }
}

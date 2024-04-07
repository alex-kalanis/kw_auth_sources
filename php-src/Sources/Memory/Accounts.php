<?php

namespace kalanis\kw_auth_sources\Sources\Memory;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Interfaces as acc_interfaces;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Traits\TLang;


/**
 * Class Accounts
 * @package kalanis\kw_auth_sources\Sources\Memory
 * Authenticate class in memory
 */
class Accounts implements acc_interfaces\IAuth, acc_interfaces\IProcessAccounts
{
    use TLang;

    protected Interfaces\IHashes $mode;
    /** @var acc_interfaces\IUser[] */
    protected array $local = [];
    /** @var array<string, string> */
    protected array $pass = [];

    /**
     * @param Interfaces\IHashes $mode
     * @param acc_interfaces\IUser[] $initial
     * @param Interfaces\IKAusTranslations|null $lang
     */
    public function __construct(Interfaces\IHashes $mode, array $initial = [], ?Interfaces\IKAusTranslations $lang = null)
    {
        $this->setAusLang($lang);
        $this->mode = $mode;
        $this->local = $initial;
        foreach ($this->local as $item) {
            $this->pass[$item->getAuthId()] = 'undefined now';
        }
    }

    public function authenticate(string $userName, array $params = []): ?acc_interfaces\IUser
    {
        if (!isset($params['password'])) {
            throw new AccountsException($this->getAusLang()->kauPassMustBeSet());
        }

        try {
            $user = $this->getDataOnly($userName);
            if ($user && isset($params['password']) && isset($this->pass[$user->getAuthId()])) {
                if ($this->mode->checkHash(strval($params['password']), $this->pass[$user->getAuthId()])) {
                    return clone $user;
                }
            }
            return null;

        } catch (AuthSourcesException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function getDataOnly(string $userName): ?acc_interfaces\IUser
    {
        foreach ($this->local as $local) {
            if ($local->getAuthName() == $userName) {
                return clone $local;
            }
        }

        return null;
    }

    public function createAccount(acc_interfaces\IUser $user, string $password): bool
    {
        foreach ($this->local as $local) {
            if ($local->getAuthName() == $user->getAuthName()) {
                return false;
            }
            if ($local->getAuthId() == $user->getAuthId()) {
                return false;
            }
        }

        try {
            $this->local[] = $user;
            $this->pass[$user->getAuthId()] = $this->mode->createHash($password);
            return true;

        } catch (AuthSourcesException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function readAccounts(): array
    {
        return $this->local;
    }

    public function updateAccount(acc_interfaces\IUser $user): bool
    {
        foreach ($this->local as $local) {
            if ($local->getAuthId() == $user->getAuthId()) {
                $local->setUserData(
                    $local->getAuthId(),
                    $user->getAuthName(),
                    $user->getGroup(),
                    $user->getClass(),
                    $user->getStatus(),
                    $user->getDisplayName(),
                    $user->getDir(),
                    $user->getExtra()
                );
                return true;
            }
        }
        return false;
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        try {
            $user = $this->getDataOnly($userName);
            if ($user && isset($this->pass[$user->getAuthId()])) {
                $this->pass[$user->getAuthId()] = $this->mode->createHash($passWord);
                return true;
            }
            return false;

        } catch (AuthSourcesException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function deleteAccount(string $userName): bool
    {
        $willDelete = null;
        $use = [];
        foreach ($this->local as $local) {
            if ($local->getAuthName() == $userName) {
                $willDelete = $local;
            } else {
                $use[] = $local;
            }
        }
        $this->local = $use;

        if ($willDelete && isset($this->pass[$willDelete->getAuthId()])) {
            unset($this->pass[$willDelete->getAuthId()]);
        }

        return !empty($willDelete);
    }
}

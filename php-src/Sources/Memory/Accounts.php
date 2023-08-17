<?php

namespace kalanis\kw_auth_sources\Sources\Memory;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Traits\TLang;


/**
 * Class Accounts
 * @package kalanis\kw_auth_sources\Sources\Memory
 * Authenticate class in memory
 */
class Accounts implements Interfaces\IAuth, Interfaces\IWorkAccounts
{
    use TLang;

    /** @var Interfaces\IHashes */
    protected $mode = null;
    /** @var Interfaces\IUser[] */
    protected $local = [];
    /** @var array<string, string> */
    protected $pass = [];

    /**
     * @param Interfaces\IHashes $mode
     * @param Interfaces\IUser[] $initial
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

    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        if (!isset($params['password'])) {
            throw new AuthSourcesException($this->getAusLang()->kauPassMustBeSet());
        }
        $user = $this->getDataOnly($userName);
        if ($user && isset($params['password']) && isset($this->pass[$user->getAuthId()])) {
            if ($this->mode->checkHash(strval($params['password']), $this->pass[$user->getAuthId()])) {
                return clone $user;
            }
        }
        return null;
    }

    public function getDataOnly(string $userName): ?Interfaces\IUser
    {
        foreach ($this->local as $local) {
            if ($local->getAuthName() == $userName) {
                return clone $local;
            }
        }

        return null;
    }

    public function createAccount(Interfaces\IUser $user, string $password): bool
    {
        foreach ($this->local as $local) {
            if ($local->getAuthName() == $user->getAuthName()) {
                return false;
            }
            if ($local->getAuthId() == $user->getAuthId()) {
                return false;
            }
        }

        $this->local[] = $user;
        $this->pass[$user->getAuthId()] = $this->mode->createHash($password);
        return true;
    }

    public function readAccounts(): array
    {
        return $this->local;
    }

    public function updateAccount(Interfaces\IUser $user): bool
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
        $user = $this->getDataOnly($userName);
        if ($user && isset($this->pass[$user->getAuthId()])) {
            $this->pass[$user->getAuthId()] = $this->mode->createHash($passWord);
            return true;
        }
        return false;
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

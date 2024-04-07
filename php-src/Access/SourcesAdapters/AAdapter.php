<?php

namespace kalanis\kw_auth_sources\Access\SourcesAdapters;


use kalanis\kw_accounts\Interfaces;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Traits\TLang;


/**
 * Class AAdapter
 * @package kalanis\kw_auth_sources\Access\SourcesAdapters
 */
abstract class AAdapter
{
    use TLang;

    protected ?Interfaces\IAuth $auth = null;
    protected ?Interfaces\IProcessAccounts $accounts = null;
    protected ?Interfaces\IProcessClasses $classes = null;
    protected ?Interfaces\IProcessGroups $groups = null;

    /**
     * @throws AuthSourcesException
     * @return Interfaces\IAuth
     */
    public function getAuth(): Interfaces\IAuth
    {
        if (!$this->auth) {
            throw new AuthSourcesException($this->getAusLang()->kauGroupMissAccounts());
        }
        return $this->auth;
    }

    /**
     * @throws AuthSourcesException
     * @return Interfaces\IProcessAccounts
     */
    public function getAccounts(): Interfaces\IProcessAccounts
    {
        if (!$this->accounts) {
            throw new AuthSourcesException($this->getAusLang()->kauGroupMissAuth());
        }
        return $this->accounts;
    }

    /**
     * @throws AuthSourcesException
     * @return Interfaces\IProcessClasses
     */
    public function getClasses(): Interfaces\IProcessClasses
    {
        if (!$this->classes) {
            throw new AuthSourcesException($this->getAusLang()->kauGroupMissClasses());
        }
        return $this->classes;
    }

    /**
     * @throws AuthSourcesException
     * @return Interfaces\IProcessGroups
     */
    public function getGroups(): Interfaces\IProcessGroups
    {
        if (!$this->groups) {
            throw new AuthSourcesException($this->getAusLang()->kauGroupMissGroups());
        }
        return $this->groups;
    }
}

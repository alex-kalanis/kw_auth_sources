<?php

namespace kalanis\kw_auth_sources\Access\SourcesAdapters;


use kalanis\kw_accounts\Interfaces;


/**
 * Class AAdapter
 * @package kalanis\kw_auth_sources\Access\SourcesAdapters
 */
abstract class AAdapter
{
    /** @var Interfaces\IAuth */
    protected $auth = null;
    /** @var Interfaces\IProcessAccounts */
    protected $accounts = null;
    /** @var Interfaces\IProcessClasses */
    protected $classes = null;
    /** @var Interfaces\IProcessGroups */
    protected $groups = null;

    public function getAuth(): Interfaces\IAuth
    {
        return $this->auth;
    }

    public function getAccounts(): Interfaces\IProcessAccounts
    {
        return $this->accounts;
    }

    public function getClasses(): Interfaces\IProcessClasses
    {
        return $this->classes;
    }

    public function getGroups(): Interfaces\IProcessGroups
    {
        return $this->groups;
    }
}

<?php

namespace kalanis\kw_auth_sources\Access\SourcesAdapters;


use kalanis\kw_auth_sources\Interfaces;


/**
 * Class AAdapter
 * @package kalanis\kw_auth_sources\Access\SourcesAdapters
 */
abstract class AAdapter
{
    /** @var Interfaces\IAuth */
    protected $auth = null;
    /** @var Interfaces\IWorkAccounts */
    protected $accounts = null;
    /** @var Interfaces\IWorkClasses */
    protected $classes = null;
    /** @var Interfaces\IWorkGroups */
    protected $groups = null;

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

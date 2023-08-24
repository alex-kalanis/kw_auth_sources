<?php

namespace kalanis\kw_auth_sources\Access\SourcesAdapters;


use kalanis\kw_accounts\Interfaces;


/**
 * Class Direct
 * @package kalanis\kw_auth_sources\Access\SourcesAdapters
 * Set that directly
 */
class Direct extends AAdapter
{
    public function __construct(Interfaces\IAuth $auth, Interfaces\IProcessAccounts $accounts, Interfaces\IProcessGroups$groups, Interfaces\IProcessClasses $classes)
    {
        $this->auth = $auth;
        $this->accounts = $accounts;
        $this->classes = $classes;
        $this->groups = $groups;
    }
}

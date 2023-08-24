<?php

namespace kalanis\kw_auth_sources\Access\SourcesAdapters;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Interfaces;


/**
 * Class FirstInstance
 * @package kalanis\kw_auth_sources\Access\SourcesAdapters
 * Set that by its first known instance
 */
class FirstInstance extends AAdapter
{
    /**
     * @param mixed ...$params
     * @throws AccountsException
     */
    public function __construct(...$params)
    {
        foreach ($params as $param) {
            if (is_object($param)) {
                if ((!$this->auth) && $param instanceof Interfaces\IAuth) {
                    $this->auth = $param;
                }
                if ((!$this->accounts) && $param instanceof Interfaces\IProcessAccounts) {
                    $this->accounts = $param;
                }
                if ((!$this->classes) && $param instanceof Interfaces\IProcessClasses) {
                    $this->classes = $param;
                }
                if ((!$this->groups) && $param instanceof Interfaces\IProcessGroups) {
                    $this->groups = $param;
                }
            }
        }

        if (!($this->auth && $this->accounts && $this->classes && $this->groups)) {
            throw new AccountsException('You must set all necessary classes in the params first!');
        }
    }
}

<?php

namespace kalanis\kw_auth_sources\Access\SourcesAdapters;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces;


/**
 * Class LastInstance
 * @package kalanis\kw_auth_sources\Access\SourcesAdapters
 * Set that by its last known instance
 */
class LastInstance extends AAdapter
{
    /**
     * @param mixed ...$params
     * @throws AuthSourcesException
     */
    public function __construct(...$params)
    {
        foreach ($params as $param) {
            if (is_object($param)) {
                if ($param instanceof Interfaces\IAuth) {
                    $this->auth = $param;
                }
                if ($param instanceof Interfaces\IWorkAccounts) {
                    $this->accounts = $param;
                }
                if ($param instanceof Interfaces\IWorkClasses) {
                    $this->classes = $param;
                }
                if ($param instanceof Interfaces\IWorkGroups) {
                    $this->groups = $param;
                }
            }
        }

        if (!($this->auth && $this->accounts && $this->classes && $this->groups)) {
            throw new AuthSourcesException('You must set all necessary classes in the params first!');
        }
    }
}

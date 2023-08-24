<?php

namespace kalanis\kw_auth_sources\Sources;


use kalanis\kw_accounts\Interfaces\IProcessClasses;


/**
 * Class Classes
 * @package kalanis\kw_auth_sources\Sources
 * Work with internal classes
 */
class Classes implements IProcessClasses
{
    /**
     * @return array<int, string>
     */
    public function readClasses(): array
    {
        return [
            IProcessClasses::CLASS_MAINTAINER => 'Maintainer',
            IProcessClasses::CLASS_ADMIN => 'Admin',
            IProcessClasses::CLASS_USER => 'User',
        ];
    }
}

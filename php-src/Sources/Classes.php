<?php

namespace kalanis\kw_auth_sources\Sources;


use kalanis\kw_auth_sources\Interfaces\IWorkClasses;


/**
 * Class Classes
 * @package kalanis\kw_auth_sources\Sources
 * Work with internal classes
 */
class Classes implements IWorkClasses
{
    /**
     * @return array<int, string>
     */
    public function readClasses(): array
    {
        return [
            IWorkClasses::CLASS_MAINTAINER => 'Maintainer',
            IWorkClasses::CLASS_ADMIN => 'Admin',
            IWorkClasses::CLASS_USER => 'User',
        ];
    }
}

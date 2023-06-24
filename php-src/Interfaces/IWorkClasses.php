<?php

namespace kalanis\kw_auth_sources\Interfaces;


/**
 * Interface IAccessClasses
 * @package kalanis\kw_auth_sources\Interfaces
 * Work with system classes
 * They are set against system and tells what is allowed to run
 */
interface IWorkClasses
{
    const CLASS_UNKNOWN = 0;
    const CLASS_MAINTAINER = 1;
    const CLASS_ADMIN = 2;
    const CLASS_USER = 3;

    /**
     * @return array<int, string>
     */
    public function readClasses(): array;
}

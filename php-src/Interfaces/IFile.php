<?php

namespace kalanis\kw_auth_sources\Interfaces;


/**
 * Interface IFile
 * @package kalanis\kw_auth_sources\Interfaces
 * Authentication file support
 */
interface IFile
{
    public const SEPARATOR = ':'; # separate params with...
    public const PASS_FILE = '.passwd'; # password file
    public const SHADE_FILE = '.shadow'; # shadow file
    public const GROUP_FILE = '.groups'; # group file
    public const CRLF = "\r\n"; # line ending
    public const PARENT_SEPARATOR = ','; # separate multiple values in one entry (like groups)
}

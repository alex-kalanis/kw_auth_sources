<?php

namespace kalanis\kw_auth_sources\Data;


use kalanis\kw_auth_sources\Interfaces\IUserCert;


/**
 * Class FileCertUser
 * @package kalanis\kw_auth_sources\Data
 */
class FileCertUser extends FileUser implements IUserCert
{
    use TCerts;
}

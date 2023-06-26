<?php

namespace kalanis\kw_auth_sources\Interfaces;


/**
 * Interface IUserCert
 * @package kalanis\kw_auth_sources\Interfaces
 * User data from your auth system - with certificate
 */
interface IUserCert extends IUser
{
    /**
     * Fill certificates; null values will not change
     * @param string $key
     * @param string $salt
     */
    public function addCertInfo(?string $key, ?string $salt): void;

    /**
     * Public salt or password to certificate or other things which uses this interface
     * @return string
     */
    public function getPubSalt(): string;

    /**
     * Certificate or other key itself
     * @return string
     */
    public function getPubKey(): string;
}

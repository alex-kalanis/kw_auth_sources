<?php

namespace kalanis\kw_auth_sources\Interfaces;


use kalanis\kw_auth_sources\AuthSourcesException;


/**
 * Interface IExpire
 * @package kalanis\kw_auth_sources\Interfaces
 * Authentication system expires at...
 */
interface IExpire
{
    /**
     * Set if authentication will expire in preset time
     * @param bool $expiry
     * @throws AuthSourcesException
     */
    public function setExpireNotice(bool $expiry): void;

    /**
     * Return if authentication expire in following time
     * @return bool
     * @throws AuthSourcesException
     */
    public function willExpire(): bool;

    /**
     * When it expire
     * @return int
     * @throws AuthSourcesException
     */
    public function getExpireTime(): int;

    /**
     * Set time of expiration
     * @param int $expireTime
     * @throws AuthSourcesException
     */
    public function updateExpireTime(int $expireTime): void;
}

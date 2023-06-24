<?php

namespace kalanis\kw_auth_sources\Data;


/**
 * Trait TExpire
 * @package kalanis\kw_auth_sources\Data
 * Expiration
 */
trait TExpire
{
    /** @var bool */
    protected $showNotice = false;
    /** @var int */
    protected $expireTime = 0;

    public function setExpireNotice(bool $expiry): void
    {
        $this->showNotice = $expiry;
    }

    public function willExpire(): bool
    {
        return $this->showNotice;
    }

    public function getExpireTime(): int
    {
        return $this->expireTime;
    }

    public function updateExpireTime(int $expireTime): void
    {
        $this->expireTime = $expireTime;
    }
}

<?php

namespace kalanis\kw_auth_sources\Data;


/**
 * Trait TCerts
 * @package kalanis\kw_auth_sources\Data
 * Work with certificates
 */
trait TCerts
{
    /** @var string */
    protected $key = '';
    /** @var string */
    protected $salt = '';

    public function addCertInfo(?string $key, ?string $salt): void
    {
        $this->key = $key ?? $this->key;
        $this->salt = $salt ?? $this->salt;
    }

    public function getPubKey(): string
    {
        return $this->key;
    }

    public function getPubSalt(): string
    {
        return $this->salt;
    }
}

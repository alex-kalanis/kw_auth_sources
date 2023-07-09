<?php

namespace kalanis\kw_auth_sources\Data;


use kalanis\kw_auth_sources\Interfaces\IUser;
use kalanis\kw_auth_sources\Interfaces\IWorkClasses;


/**
 * Class FileUser
 * @package kalanis\kw_auth_sources\Data
 */
class FileUser implements IUser
{
    /** @var string */
    protected $authId = '0';
    /** @var string */
    protected $authName = '';
    /** @var string */
    protected $authGroup = '0';
    /** @var int */
    protected $authClass = IWorkClasses::CLASS_UNKNOWN;
    /** @var int */
    protected $authStatus = IUser::USER_STATUS_UNKNOWN;
    /** @var string */
    protected $displayName = '';
    /** @var string */
    protected $dir = '';
    /** @var array<string|int, string|int|float|bool> */
    protected $extra = [];

    public function setUserData(?string $authId, ?string $authName, ?string $authGroup, ?int $authClass, ?int $authStatus, ?string $displayName, ?string $dir, ?array $extra = []): void
    {
        $this->authId = $authId ?? $this->authId;
        $this->authName = $authName ?? $this->authName;
        $this->authGroup = $authGroup ?? $this->authGroup;
        $this->authClass = $authClass ?? $this->authClass;
        $this->authStatus = $authStatus ?? $this->authStatus;
        $this->displayName = $displayName ?? $this->displayName;
        $this->dir = $dir ?? $this->dir;
        $this->extra = !is_null($extra) ? array_merge($this->extra, $extra) : $this->extra;
    }

    public function getAuthId(): string
    {
        return $this->authId;
    }

    public function getAuthName(): string
    {
        return $this->authName;
    }

    public function getGroup(): string
    {
        return $this->authGroup;
    }

    public function getClass(): int
    {
        return $this->authClass;
    }

    public function getStatus(): int
    {
        return $this->authStatus;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }
}

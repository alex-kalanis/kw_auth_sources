<?php

namespace kalanis\kw_auth_sources\Sources\Mapper\Database;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces\IWorkClasses;
use kalanis\kw_auth_sources\Interfaces\IUserCert;
use kalanis\kw_mapper\Interfaces\IEntryType;
use kalanis\kw_mapper\Records\ASimpleRecord;


/**
 * Class UsersRecord
 * @package kalanis\kw_auth_sources\Sources\Mapper\Database
 * @property string $id
 * @property string $login
 * @property string $pass
 * @property string $groupId
 * @property string $display
 * @property string $cert
 * @property string $salt
 * @property array<string|int, string|int|float|bool> $extra
 * @property GroupsRecord[] $groups
 * @codeCoverageIgnore remote source
 * Mainly seen as example, not working thing due necessity of knowing database structure
 */
class UsersRecord extends ASimpleRecord implements IUserCert
{
    public function addEntries(): void
    {
        $this->addEntry('id', IEntryType::TYPE_STRING, 2048);
        $this->addEntry('login', IEntryType::TYPE_STRING, 512);
        $this->addEntry('pass', IEntryType::TYPE_STRING, 512);
        $this->addEntry('groupId', IEntryType::TYPE_STRING, 512);
        $this->addEntry('display', IEntryType::TYPE_STRING, 512);
        $this->addEntry('cert', IEntryType::TYPE_STRING, 8192);
        $this->addEntry('salt', IEntryType::TYPE_STRING, 1024);
        $this->addEntry('extra', IEntryType::TYPE_ARRAY, []);
        $this->addEntry('groups', IEntryType::TYPE_ARRAY, []);
        $this->setMapper(UsersMapper::class);
    }

    public function setUserData(?string $authId, ?string $authName, ?string $authGroup, ?int $authClass, ?int $authStatus, ?string $displayName, ?string $dir, ?array $extra = []): void
    {
        if (empty($authId)) {
            throw new AuthSourcesException('No user ID');
        }
        $this->id = $authId;
        $this->load();
        $this->login = $authName ?? $this->login;
        $this->groupId = $authGroup ?? $this->groupId;
        $this->display = $displayName ?? $this->display;
        $this->extra = !is_null($extra) ? array_merge($this->extra, $extra) : $this->extra;
        $this->save();
    }

    public function addCertInfo(?string $key, ?string $salt): void
    {
        $this->load();
        $this->cert = $key ?? $this->cert;
        $this->salt = $salt ?? $this->salt;
        $this->save();
    }

    public function getAuthId(): string
    {
        return strval($this->id);
    }

    public function getAuthName(): string
    {
        return strval($this->login);
    }

    public function getGroup(): string
    {
        return strval($this->groupId);
    }

    public function getClass(): int
    {
        return IWorkClasses::CLASS_USER;
    }

    public function getStatus(): int
    {
        return static::USER_STATUS_ENABLED;
    }

    public function getDisplayName(): string
    {
        return strval($this->display);
    }

    public function getDir(): string
    {
        return '/';
    }

    public function getExtra(): array
    {
        return (array) $this->extra;
    }

    public function getPubSalt(): string
    {
        return strval($this->salt);
    }

    public function getPubKey(): string
    {
        return strval($this->cert);
    }
}

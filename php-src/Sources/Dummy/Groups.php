<?php

namespace kalanis\kw_auth_sources\Sources\Dummy;


use kalanis\kw_auth_sources\Interfaces;


/**
 * Class Groups
 * @package kalanis\kw_auth_sources\Sources\Dummy
 * Work with groups of users - but not with this one
 */
class Groups implements Interfaces\IWorkGroups
{

    public function createGroup(Interfaces\IGroup $group): bool
    {
        return false;
    }

    public function getGroupDataOnly(string $groupId): ?Interfaces\IGroup
    {
        return null;
    }

    public function readGroup(): array
    {
        return [];
    }

    public function updateGroup(Interfaces\IGroup $group): bool
    {
        return false;
    }

    public function deleteGroup(string $groupId): bool
    {
        return false;
    }
}

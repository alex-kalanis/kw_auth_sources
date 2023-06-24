<?php

namespace kalanis\kw_auth_sources\Interfaces;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_locks\LockException;


/**
 * Interface IWorkGroups
 * @package kalanis\kw_auth_sources\Interfaces
 * Work with groups
 * They are set against each user and say what is allowed to access
 */
interface IWorkGroups
{
    /**
     * @param IGroup $group
     * @throws AuthSourcesException
     * @throws LockException
     * @return bool
     */
    public function createGroup(IGroup $group): bool;

    /**
     * @param string $groupId
     * @throws AuthSourcesException
     * @throws LockException
     * @return IGroup|null
     */
    public function getGroupDataOnly(string $groupId): ?IGroup;

    /**
     * @throws AuthSourcesException
     * @throws LockException
     * @return IGroup[]
     */
    public function readGroup(): array;

    /**
     * @param IGroup $group
     * @throws AuthSourcesException
     * @throws LockException
     * @return bool
     */
    public function updateGroup(IGroup $group): bool;

    /**
     * @param string $groupId
     * @throws AuthSourcesException
     * @throws LockException
     * @return bool
     */
    public function deleteGroup(string $groupId): bool;
}

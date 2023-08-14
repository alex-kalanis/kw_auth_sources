<?php

namespace kalanis\kw_auth_sources\Sources\Memory;


use kalanis\kw_auth_sources\Interfaces;


/**
 * Class Groups
 * @package kalanis\kw_auth_sources\Sources\Memory
 * Work with groups of users - in memory
 */
class Groups implements Interfaces\IWorkGroups
{
    /** @var Interfaces\IGroup[] */
    protected $local = [];

    /**
     * @param Interfaces\IGroup[] $initial
     */
    public function __construct(array $initial = [])
    {
        $this->local = $initial;
    }

    public function createGroup(Interfaces\IGroup $group): bool
    {
        foreach ($this->local as $local) {
            if ($local->getGroupId() == $group->getGroupId()) {
                return false;
            }
        }

        $this->local[] = $group;
        return true;
    }

    public function getGroupDataOnly(string $groupId): ?Interfaces\IGroup
    {
        foreach ($this->local as $local) {
            if ($local->getGroupId() == $groupId) {
                return clone $local;
            }
        }
        return null;
    }

    public function readGroup(): array
    {
        return $this->local;
    }

    public function updateGroup(Interfaces\IGroup $group): bool
    {
        foreach ($this->local as $local) {
            if ($local->getGroupId() == $group->getGroupId()) {
                $local->setGroupData(
                    $local->getGroupId(),
                    $group->getGroupName(),
                    $group->getGroupDesc(),
                    $local->getGroupAuthorId(),
                    $group->getGroupStatus(),
                    $group->getGroupParents(),
                    $group->getGroupExtra()
                );
                return true;
            }
        }
        return false;
    }

    public function deleteGroup(string $groupId): bool
    {
        $willDelete = false;
        $use = [];
        foreach ($this->local as $local) {
            if ($local->getGroupId() == $groupId) {
                $willDelete = true;
            } else {
                $use[] = $local;
            }
        }
        $this->local = $use;
        return $willDelete;
    }
}

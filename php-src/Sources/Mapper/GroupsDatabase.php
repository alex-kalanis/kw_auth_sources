<?php

namespace kalanis\kw_auth_sources\Sources\Mapper;


use kalanis\kw_accounts\Interfaces;
use kalanis\kw_auth_sources\Traits\TSeparated;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Search\Search;


/**
 * Class GroupsDatabase
 * @package kalanis\kw_auth_sources\Sources\Mapper
 * Authenticate via Database
 * need kw_mapper!
 * @codeCoverageIgnore because access external content
 */
class GroupsDatabase implements Interfaces\IProcessGroups
{
    use TSeparated;

    /** @var Database\GroupsRecord */
    protected $groupsRecord = null;
    /** @var Database\UsersRecord */
    protected $usersRecord = null;

    public function __construct()
    {
        $this->groupsRecord = new Database\GroupsRecord();
        $this->usersRecord = new Database\UsersRecord();
    }

    /**
     * @param Interfaces\IGroup $group
     * @throws MapperException
     * @return bool
     */
    public function createGroup(Interfaces\IGroup $group): bool
    {
        $record = clone $this->groupsRecord;
        $record->name = $group->getGroupName();
        $record->desc = $group->getGroupDesc();
        $record->authorId = $group->getGroupAuthorId();
        $record->parents = $this->compactStr($group->getGroupParents());
        $record->status = $group->getGroupStatus();
        return $record->save(true);
    }

    /**
     * @param string $groupId
     * @throws MapperException
     * @return Interfaces\IGroup|null
     */
    public function getGroupDataOnly(string $groupId): ?Interfaces\IGroup
    {
        $record = clone $this->groupsRecord;
        $record->id = $groupId;
        if (empty($record->count())) {
            return null;
        }
        $record->load();
        return $record;
    }

    /**
     * @throws MapperException
     * @return Database\GroupsRecord[]
     */
    public function readGroup(): array
    {
        $search = new Search(clone $this->groupsRecord);
        return $search->getResults();
    }

    /**
     * @param Interfaces\IGroup $group
     * @throws MapperException
     * @return bool
     */
    public function updateGroup(Interfaces\IGroup $group): bool
    {
        $record = clone $this->groupsRecord;
        $record->id = $group->getGroupId();
        $record->load();
        $record->name = $group->getGroupName();
        $record->desc = $group->getGroupDesc();
        $record->parents = $this->compactStr($group->getGroupParents());
        $record->status = $group->getGroupStatus();
        return $record->save();
    }

    /**
     * @param string $groupId
     * @throws MapperException
     * @return bool
     */
    public function deleteGroup(string $groupId): bool
    {
        $users = clone $this->usersRecord;
        $users->groupId = $groupId;
        if (0 >= $users->count()) {
            // not empty group
            return false;
        }
        $record = clone $this->groupsRecord;
        $record->id = $groupId;
        return $record->delete();
    }
}

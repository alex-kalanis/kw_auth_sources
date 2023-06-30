<?php

namespace kalanis\kw_auth_sources\Sources\Mapper\Database;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Class GroupsMapper
 * @package kalanis\kw_auth_sources\Data\Mapper\Database
 * @codeCoverageIgnore remote source
 */
class GroupsMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('default_database');
        $this->setTable('groups');
        $this->setRelation('id', 'gr_id');
        $this->setRelation('name', 'gr_name');
        $this->setRelation('authorId', 'u_id');
        $this->setRelation('parents', 'gr_parents');
        $this->setRelation('desc', 'gr_desc');
        $this->setRelation('status', 'gr_status');
        $this->setRelation('extra', 'gr_extra');
        $this->addPrimaryKey('id');
        $this->addForeignKey('authors', UsersRecord::class, 'authorId', 'id');
        $this->addForeignKey('members', UsersRecord::class, 'id', 'groupId');
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function afterLoad(ARecord $record): bool
    {
        $entry = $record->getEntry('extra');
        $entry->setData(json_decode(strval($entry->getData()), true), true);
        return parent::afterLoad($record);
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function beforeInsert(ARecord $record): bool
    {
        $entry = $record->getEntry('extra');
        $entry->setData(json_encode($entry->getData()));
        return parent::beforeInsert($record);
    }

    /**
     * @param ARecord $record
     * @throws MapperException
     * @return bool
     */
    protected function beforeUpdate(ARecord $record): bool
    {
        $entry = $record->getEntry('extra');
        $entry->setData(json_encode($entry->getData()));
        return parent::beforeUpdate($record);
    }
}

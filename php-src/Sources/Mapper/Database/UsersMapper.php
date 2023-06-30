<?php

namespace kalanis\kw_auth_sources\Sources\Mapper\Database;


use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Mappers\Database\ADatabase;
use kalanis\kw_mapper\Records\ARecord;


/**
 * Class UsersMapper
 * @package kalanis\kw_auth_sources\Data\Mapper\Database
 * @codeCoverageIgnore remote source
 */
class UsersMapper extends ADatabase
{
    protected function setMap(): void
    {
        $this->setSource('default_database');
        $this->setTable('users');
        $this->setRelation('id', 'u_id');
        $this->setRelation('login', 'u_login');
        $this->setRelation('pass', 'u_pass');
        $this->setRelation('groupId', 'gr_id');
        $this->setRelation('display', 'u_display');
        $this->setRelation('cert', 'u_cert');
        $this->setRelation('salt', 'u_salt');
        $this->setRelation('extra', 'u_extra');
        $this->addPrimaryKey('id');
        $this->addForeignKey('groups', GroupsRecord::class, 'groupId', 'id');
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

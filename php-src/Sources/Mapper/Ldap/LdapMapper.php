<?php

namespace kalanis\kw_auth_sources\Sources\Mapper\Ldap;


use kalanis\kw_mapper\Mappers\Database\ALdap;


/**
 * Class LdapMapper
 * @package kalanis\kw_auth_sources\Sources\Mapper\Ldap
 * @codeCoverageIgnore remote source
 */
class LdapMapper extends ALdap
{
    protected function setMap(): void
    {
        $this->setSource('ldap');
        $this->setTable('user');
        $this->setRelation('id', 'id');
        $this->setRelation('name', 'name');
        $this->addPrimaryKey('id');
    }
}

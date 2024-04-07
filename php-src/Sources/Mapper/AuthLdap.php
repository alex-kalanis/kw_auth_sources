<?php

namespace kalanis\kw_auth_sources\Sources\Mapper;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Interfaces\IAuth;
use kalanis\kw_accounts\Interfaces\IUser;
use kalanis\kw_mapper\MapperException;


/**
 * Class AuthLdap
 * @package kalanis\kw_auth_sources\Sources\Mapper
 * Authenticate via ldap
 * need kw_mapper!
 * @codeCoverageIgnore because access external content
 */
class AuthLdap implements IAuth
{
    protected Ldap\LdapRecord $record;

    public function __construct()
    {
        $this->record = new Ldap\LdapRecord();
    }

    public function authenticate(string $userName, array $params = []): ?IUser
    {
        try {
            $mapper = $this->record->getMapper();
            if (!method_exists($mapper, 'authorize')) {
                return null;
            }
            /** @var Ldap\LdapMapper $mapper */
            return ($mapper->authorize([
                'user' => strval($userName),
                'password' => strval($params['password'] ?: '')
            ]))
                ? $this->getDataOnly($userName)
                : null ;
        } catch (MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function getDataOnly(string $userName): ?IUser
    {
        try {
            $record = clone $this->record;
            $record->name = $userName;
            $record->load();
            return (empty($record->getAuthId())) ? null : $record ;
        } catch (MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }
}

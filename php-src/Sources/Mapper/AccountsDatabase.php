<?php

namespace kalanis\kw_auth_sources\Sources\Mapper;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Interfaces as acc_interfaces;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_auth_sources\Traits\TSeparated;
use kalanis\kw_mapper\MapperException;
use kalanis\kw_mapper\Search\Search;


/**
 * Class Database
 * @package kalanis\kw_auth_sources\Sources\Mapper
 * Authenticate via Database
 * need kw_mapper!
 * @codeCoverageIgnore because access external content
 */
class AccountsDatabase implements acc_interfaces\IAuthCert, acc_interfaces\IProcessAccounts
{
    use TLang;
    use TSeparated;

    protected Interfaces\IHashes $passMode;
    protected Database\UsersRecord $usersRecord;

    public function __construct(Interfaces\IHashes $mode, ?Interfaces\IKAusTranslations $lang = null)
    {
        $this->setAusLang($lang);
        $this->passMode = $mode;
        $this->usersRecord = new Database\UsersRecord();
    }

    public function authenticate(string $userName, array $params = []): ?acc_interfaces\IUser
    {
        if (!isset($params['password'])) {
            throw new AccountsException($this->getAusLang()->kauPassMustBeSet());
        }
        try {
            $record = $this->getByLogin($userName);
            if (empty($record)) {
                return null;
            }

            if (!$this->passMode->checkHash(isset($params['password']) ? strval($params['password']): '', $record->pass)) {
                return null;
            }
            return $record;

        } catch (AuthSourcesException | MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function getDataOnly(string $userName): ?acc_interfaces\IUser
    {
        try {
            return $this->getByLogin($userName);

        } catch (MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function updateCertData(string $userName, ?string $certKey, ?string $certSalt): bool
    {
        try {
            $record = clone $this->usersRecord;
            $record->login = $userName;
            $record->load();
            $record->cert = strval($certKey);
            $record->salt = strval($certSalt);
            return $record->save();

        } catch (MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function getCertData(string $userName): ?acc_interfaces\ICert
    {
        try {
            return $this->getByLogin($userName);

        } catch (MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @param string $login
     * @throws MapperException
     * @return Database\UsersRecord|null
     */
    protected function getByLogin(string $login): ?Database\UsersRecord
    {
        $record = clone $this->usersRecord;
        $record->login = $login;
        if (empty($record->count())) {
            return null;
        }
        $record->load();
        return $record;
    }

    public function createAccount(acc_interfaces\IUser $user, string $password): bool
    {
        try {
            $record = clone $this->usersRecord;
            $this->checkLogin($user->getAuthName());
            $record->login = $user->getAuthName();
            $record->groupId = $user->getGroup();
            $record->display = $user->getDisplayName();
            return $record->save(true);

        } catch (MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @throws AccountsException
     * @return Database\UsersRecord[]
     */
    public function readAccounts(): array
    {
        try {
            $search = new Search(clone $this->usersRecord);
            return $search->getResults();

        } catch (MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function updateAccount(acc_interfaces\IUser $user): bool
    {
        try {
            $this->checkLogin($user->getAuthName(), $user->getAuthId());
            $record = clone $this->usersRecord;
            $record->id = $user->getAuthId();
            $record->load();
            $record->login = $user->getAuthName();
            $record->groupId = $user->getGroup();
            $record->display = $user->getDisplayName();
            return $record->save();

        } catch (MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function updatePassword(string $userName, string $passWord): bool
    {
        try {
            $record = clone $this->usersRecord;
            $record->login = $userName;
            $record->load();
            $record->pass = $this->passMode->createHash($passWord);
            return $record->save();

        } catch (AuthSourcesException | MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function deleteAccount(string $userName): bool
    {
        try {
            $record = clone $this->usersRecord;
            $record->login = $userName;
            return $record->delete();

        } catch (MapperException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @param string $login
     * @param string $id
     * @throws AccountsException
     * @throws MapperException
     */
    protected function checkLogin(string $login, string $id = '0'): void
    {
        $user = clone $this->usersRecord;
        $user->login = $login;
        $amount = $user->count();
        if (1 > $amount) {
            return;
        }
        if (1 < $amount) {
            throw new AccountsException('Too many users with that login!');
        }
        $user->load();
        if ($id && ($user->id != $id)) {
            throw new AccountsException('Login already used.');
        }
    }
}

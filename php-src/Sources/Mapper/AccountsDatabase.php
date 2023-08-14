<?php

namespace kalanis\kw_auth_sources\Sources\Mapper;


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
class AccountsDatabase implements Interfaces\IAuthCert, Interfaces\IWorkAccounts
{
    use TLang;
    use TSeparated;

    /** @var Interfaces\IHashes */
    protected $passMode = null;
    /** @var Database\UsersRecord */
    protected $usersRecord = null;

    public function __construct(Interfaces\IHashes $mode, ?Interfaces\IKAusTranslations $lang = null)
    {
        $this->setAusLang($lang);
        $this->passMode = $mode;
        $this->usersRecord = new Database\UsersRecord();
    }

    /**
     * @param string $userName
     * @param array<string, string|int|float> $params
     * @throws AuthSourcesException
     * @throws MapperException
     * @return Interfaces\IUser|null
     */
    public function authenticate(string $userName, array $params = []): ?Interfaces\IUser
    {
        if (!isset($params['password'])) {
            throw new AuthSourcesException($this->getAusLang()->kauPassMustBeSet());
        }
        $record = $this->getByLogin($userName);
        if (empty($record)) {
            return null;
        }
        if (!$this->passMode->checkHash(isset($params['password']) ? strval($params['password']): '', $record->pass)) {
            return null;
        }
        return $record;
    }

    /**
     * @param string $userName
     * @throws MapperException
     * @return Interfaces\IUser|null
     */
    public function getDataOnly(string $userName): ?Interfaces\IUser
    {
        return $this->getByLogin($userName);
    }

    /**
     * @param string $userName
     * @param string|null $certKey
     * @param string|null $certSalt
     * @throws MapperException
     * @return bool
     */
    public function updateCertKeys(string $userName, ?string $certKey, ?string $certSalt): bool
    {
        $record = clone $this->usersRecord;
        $record->login = $userName;
        $record->load();
        $record->cert = strval($certKey);
        $record->salt = strval($certSalt);
        return $record->save();
    }

    /**
     * @param string $userName
     * @throws MapperException
     * @return Interfaces\IUserCert|null
     */
    public function getCertData(string $userName): ?Interfaces\IUserCert
    {
        return $this->getByLogin($userName);
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

    /**
     * @param Interfaces\IUser $user
     * @param string $password
     * @throws AuthSourcesException
     * @throws MapperException
     * @return bool
     */
    public function createAccount(Interfaces\IUser $user, string $password): bool
    {
        $record = clone $this->usersRecord;
        $this->checkLogin($user->getAuthName());
        $record->login = $user->getAuthName();
        $record->groupId = $user->getGroup();
        $record->display = $user->getDisplayName();
        return $record->save(true);
    }

    /**
     * @throws MapperException
     * @return Database\UsersRecord[]
     */
    public function readAccounts(): array
    {
        $search = new Search(clone $this->usersRecord);
        return $search->getResults();
    }

    /**
     * @param Interfaces\IUser $user
     * @throws AuthSourcesException
     * @throws MapperException
     * @return bool
     */
    public function updateAccount(Interfaces\IUser $user): bool
    {
        $this->checkLogin($user->getAuthName(), $user->getAuthId());
        $record = clone $this->usersRecord;
        $record->id = $user->getAuthId();
        $record->load();
        $record->login = $user->getAuthName();
        $record->groupId = $user->getGroup();
        $record->display = $user->getDisplayName();
        return $record->save();
    }

    /**
     * @param string $userName
     * @param string $passWord
     * @throws AuthSourcesException
     * @throws MapperException
     * @return bool
     */
    public function updatePassword(string $userName, string $passWord): bool
    {
        $record = clone $this->usersRecord;
        $record->login = $userName;
        $record->load();
        $record->pass = $this->passMode->createHash($passWord);
        return $record->save();
    }

    /**
     * @param string $userName
     * @throws MapperException
     * @return bool
     */
    public function deleteAccount(string $userName): bool
    {
        $record = clone $this->usersRecord;
        $record->login = $userName;
        return $record->delete();
    }

    /**
     * @param string $login
     * @param string $id
     * @throws AuthSourcesException
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
            throw new AuthSourcesException('Too many users with that login!');
        }
        $user->load();
        if ($id && ($user->id != $id)) {
            throw new AuthSourcesException('Login already used.');
        }
    }
}

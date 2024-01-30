<?php

namespace SourcesTests\Files;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Data\FileCertUser;
use kalanis\kw_accounts\Interfaces\IUser;
use kalanis\kw_locks\LockException;


class AccountsMultiFilesTest extends AFilesTest
{
    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testNotExistsData(): void
    {
        $lib = $this->emptyFilesSources();
        $this->assertNull($lib->getDataOnly('does not exist'));
        $this->assertNull($lib->getCertData('does not exist'));
        $this->assertNull($lib->authenticate('does not exist', ['password' => 'not need']));
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testDataOnly(): void
    {
        $lib = $this->fullFilesSources();
        $this->assertEmpty($lib->getDataOnly('does not exist'));
        $this->assertEmpty($lib->getCertData('does not exist'));
        $user = $lib->getDataOnly('manager');
        $this->assertNotEmpty($user);
        $this->assertEquals('Manage', $user->getDisplayName());
        $this->assertEquals([
            'hint' => 'Uncut',
            'age' => 39,
            'powers' => ['foo', 'bar', 'baz',]
        ], $user->getExtra());
    }

    /**
     * @throws AccountsException
     */
    public function testDataOnlyLocked(): void
    {
        $lib = $this->lockFailFilesSources();
        $this->expectException(AccountsException::class);
        $lib->getDataOnly('manager');
    }

    /**
     * @throws AccountsException
     */
    public function testCertDataOnlyLocked(): void
    {
        $lib = $this->lockFailFilesSources();
        $this->expectException(AccountsException::class);
        $lib->getCertData('manager');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testAuthenticate(): void
    {
        $lib = $this->fullFilesSources();
        $this->assertEmpty($lib->authenticate('manager', ['password' => 'thisisnotreal']));
        $user = $lib->authenticate('manager', ['password' => 'valid']);
        $this->assertNotEmpty($user);
        $this->assertEquals('Manage', $user->getDisplayName());
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testAuthenticateNoPass(): void
    {
        $lib = $this->fullFilesSources();
        $this->expectException(AccountsException::class);
        $lib->authenticate('manager', []);
    }

    /**
     * @throws AccountsException
     */
    public function testAuthenticateLocked(): void
    {
        $lib = $this->lockFailFilesSources();
        $this->expectException(AccountsException::class);
        $lib->authenticate('manager', ['password' => 'valid']);
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testCreateAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFilesSources();
        $user = $this->wantedUser();

        // create
        $lib->createAccount($user, 'here to set');

        // check data
        $saved = $lib->getDataOnly($user->getAuthName());
        $this->assertEquals('Testing another', $saved->getDisplayName());
        $this->assertEquals('why_here', $saved->getDir());
        $this->assertEquals(3, $saved->getClass());
    }

    /**
     * @throws AccountsException
     */
    public function testCreateAccountLocked(): void
    {
        $lib = $this->lockFailFilesSources();
        $user = $this->wantedUser();

        // create
        $this->expectException(AccountsException::class);
        $lib->createAccount($user, 'here to set');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testUpdateAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFilesSources();
        $user = $this->wantedUser();

        $this->expectException(AccountsException::class);
        $lib->updateAccount($user);
    }

    /**
     * @throws AccountsException
     */
    public function testUpdateAccountLocked(): void
    {
        $lib = $this->lockFailFilesSources();
        $user = $this->wantedUser();

        $this->expectException(AccountsException::class);
        $lib->updateAccount($user);
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testUpdatePasswordOnEmptyInstance(): void
    {
        $lib = $this->emptyFilesSources();

        $this->expectException(AccountsException::class);
        $lib->updatePassword('someone', 'not important');
    }

    /**
     * @throws AccountsException
     */
    public function testUpdatePasswordLocked(): void
    {
        $lib = $this->lockFailFilesSources();

        $this->expectException(AccountsException::class);
        $lib->updatePassword('manager', 'not important');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testUpdateCertsOnEmptyInstance(): void
    {
        $lib = $this->emptyFilesSources();

        $this->expectException(AccountsException::class);
        $lib->updateCertData('someone', 'can be empty in this case', 'not important');
    }

    /**
     * @throws AccountsException
     */
    public function testUpdateCertsLocked(): void
    {
        $lib = $this->lockFailFilesSources();

        $this->expectException(AccountsException::class);
        $lib->updateCertData('manager', 'can be empty in this case', 'not important');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testDeleteAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFilesSources();
        $user = $this->wantedUser();

        $this->expectException(AccountsException::class);
        $lib->deleteAccount($user->getAuthName());
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testDeleteAccountOnPartialInstance(): void
    {
        $lib = $this->partialFilesSources();
        $user = $this->wantedUser();

        $this->expectException(AccountsException::class);
        $lib->deleteAccount($user->getAuthName());
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testDeleteAccountLocked(): void
    {
        $lib = $this->lockFailFilesSources();
        $user = $this->wantedUser();

        $this->expectException(AccountsException::class);
        $lib->deleteAccount($user->getAuthName());
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testAccountManipulation(): void
    {
        $lib = $this->fullFilesSources();
        $user = $this->wantedUser();

        // create
        $lib->createAccount($user, 'here to set');
        // check data
        $saved = $lib->getDataOnly($user->getAuthName());
        $this->assertEquals('Testing another', $saved->getDisplayName());
        $this->assertEquals('why_here', $saved->getDir());
        $this->assertEquals(3, $saved->getClass());

        // check login
        $this->assertNotEmpty($lib->authenticate($user->getAuthName(), ['password' => 'here to set']));

        // update
        $user->setUserData(
            null,
            null,
            null,
            2,
            3,
            'WheĐn yoĐu dđo nođt knđow',
            null
        );
        $user->updateCertInfo('==public key for accessing that content==', 'hidden salt');
        $lib->updateAccount($user);
        $lib->updateCertData($user->getAuthName(), $user->getPubKey(), $user->getSalt());

        // update name
        $user->setUserData(
            null,
            'changed name',
            null,
            null,
            null,
            null,
            null
        );
        $lib->updateAccount($user);

        // check data - again with new values
        $saved = $lib->getCertData($user->getAuthName());
        $this->assertEquals('When you do not know', $saved->getDisplayName());
        $this->assertEquals(2, $saved->getClass());
        $this->assertEquals($user->getPubKey(), $saved->getPubKey());
        $this->assertEquals($user->getSalt(), $saved->getSalt());


        // update password
        $lib->updatePassword($user->getAuthName(), 'another pass');
        // check login
        $this->assertEmpty($lib->authenticate($user->getAuthName(), ['password' => 'here to set']));
        $this->assertNotEmpty($lib->authenticate($user->getAuthName(), ['password' => 'another pass']));

        // remove
        $lib->deleteAccount($user->getAuthName());
        // check for existence
        $this->assertEmpty($lib->getDataOnly($user->getAuthName()));
    }

    /**
     * @throws AccountsException
     * @throws LockException
     * AuthId is not correct but auth name is
     */
    public function testAccountUpdateFail(): void
    {
        $lib = $this->fullFilesSources();
        $user = new FileCertUser();
        $user->setUserData('600', 'worker', '0', 0, 2, 'Die on set', 'so_here');

        $this->expectException(AccountsException::class);
        $lib->updateAccount($user);
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testCreateFail(): void
    {
        $lib = $this->fullFilesSources();
        $user = $this->wantedUser();
        $this->expectException(AccountsException::class);
        $lib->createAccount($user, '');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testAllUsers(): void
    {
        $lib = $this->fullFilesSources();
        /** @var IUser[] $data */
        $data = $lib->readAccounts();
        $this->assertEquals(1, $data[0]->getClass());
        $this->assertEquals('manager', $data[1]->getAuthName());
    }

    /**
     * @throws AccountsException
     */
    public function testAllUsersLocked(): void
    {
        $lib = $this->lockFailFilesSources();
        $this->expectException(AccountsException::class);
        $lib->readAccounts();
    }

    protected function wantedUser(): FileCertUser
    {
        $user = new FileCertUser();
        $user->setUserData('1003', 'another', '0', 0, 1, 'Testing another', 'why_here');
        return $user;
    }
}

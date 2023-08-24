<?php

namespace SourcesTests\Files;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Data\FileUser;
use kalanis\kw_accounts\Interfaces\IUser;
use kalanis\kw_locks\LockException;


class AccountsSingleFileTest extends AFilesTest
{
    protected $sourcePath = ['data', '.passcomb'];

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testNotExistsData(): void
    {
        $lib = $this->emptyFileSources();
        $this->assertNull($lib->getDataOnly('does not exist'));
        $this->assertNull($lib->authenticate('does not exist', ['password' => 'not need']));
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testDataOnly(): void
    {
        $lib = $this->partialFileSources();
        $this->assertEmpty($lib->getDataOnly('does not exist'));
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
        $lib = $this->lockFailFileSources();
        $this->expectException(AccountsException::class);
        $lib->getDataOnly('does not exist');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testAuthenticate(): void
    {
        $lib = $this->partialFileSources();
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
        $lib = $this->partialFileSources();
        $this->expectException(AccountsException::class);
        $lib->authenticate('manager', []);
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testAuthenticateLocked(): void
    {
        $lib = $this->lockFailFileSources();
        $this->expectException(AccountsException::class);
        $lib->authenticate('manager', ['password' => 'valid']);
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testCreateAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        $user = $this->wantedUser();

        // create
        $this->assertTrue($lib->createAccount($user, 'here to set'));

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
        $lib = $this->lockFailFileSources();
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
        $lib = $this->emptyFileSources();
        $user = $this->wantedUser();

        // update
        $this->expectException(AccountsException::class);
        $lib->updateAccount($user);
    }

    /**
     * @throws AccountsException
     */
    public function testUpdateAccountLocked(): void
    {
        $lib = $this->lockFailFileSources();
        $user = $this->wantedUser();

        // update
        $this->expectException(AccountsException::class);
        $lib->updateAccount($user);
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testUpdatePasswordOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        // update
        $this->expectException(AccountsException::class);
        $lib->updatePassword('This user does not exists', 'not important');
    }

    /**
     * @throws AccountsException
     */
    public function testUpdatePasswordLocked(): void
    {
        $lib = $this->lockFailFileSources();
        // update
        $this->expectException(AccountsException::class);
        $lib->updatePassword('manager', 'not important');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testRemoveAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        $user = $this->wantedUser();

        // delete
        $lib->deleteAccount($user->getAuthName());
        $this->assertNull($lib->getDataOnly($user->getAuthName()));
    }

    /**
     * @throws AccountsException
     */
    public function testRemoveAccountLocked(): void
    {
        $lib = $this->lockFailFileSources();
        $user = $this->wantedUser();

        // delete
        $this->expectException(AccountsException::class);
        $lib->deleteAccount($user->getAuthName());
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testAccountManipulation(): void
    {
        $lib = $this->partialFileSources();
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
        $lib->updateAccount($user);

        // check data - again with new values
        $saved = $lib->getDataOnly($user->getAuthName());
        $this->assertEquals('When you do not know', $saved->getDisplayName());
        $this->assertEquals(2, $saved->getClass());

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
     */
    public function testCreateFail(): void
    {
        $lib = $this->partialFileSources();
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
        $lib = $this->partialFileSources();
        $data = $lib->readAccounts();
        /** @var IUser[] $data */
        $this->assertEquals(1, $data[0]->getClass());
        $this->assertEquals('manager', $data[1]->getAuthName());
    }

    /**
     * @throws AccountsException
     */
    public function testAllUsersLocked(): void
    {
        $lib = $this->lockFailFileSources();
        $this->expectException(AccountsException::class);
        $lib->readAccounts();
    }

    protected function wantedUser(): FileUser
    {
        $user = new FileUser();
        $user->setUserData('600', 'another', '0', 0, 2,'Testing another', 'why_here');
        return $user;
    }
}

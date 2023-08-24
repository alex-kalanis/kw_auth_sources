<?php

namespace SourcesTests\Memory;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Data\FileUser;
use kalanis\kw_accounts\Interfaces\IUser;


class AccountsTest extends AMemoryTest
{
    /**
     * @throws AccountsException
     */
    public function testNotExistsData(): void
    {
        $lib = $this->emptyFileSources();
        $this->assertNull($lib->getDataOnly('does not exist'));
        $this->assertNull($lib->authenticate('does not exist', ['password' => 'not need']));
    }

    /**
     * @throws AccountsException
     */
    public function testDataOnly(): void
    {
        $lib = $this->fullFileSources();
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
    public function testAuthenticate(): void
    {
        $lib = $this->fullFileSources();
        $this->assertEmpty($lib->authenticate('manager', ['password' => 'thisisnotreal']));
        $user = $lib->authenticate('manager', ['password' => 'valid']);
        $this->assertNotEmpty($user);
        $this->assertEquals('Manage', $user->getDisplayName());
    }

    /**
     * @throws AccountsException
     */
    public function testAuthenticateNoPass(): void
    {
        $lib = $this->fullFileSources();
        $this->expectException(AccountsException::class);
        $lib->authenticate('manager', []);
    }

    /**
     * @throws AccountsException
     */
    public function testAuthenticateOnFailedHash(): void
    {
        $lib = $this->failedFileSources();
        $this->expectException(AccountsException::class);
        $lib->authenticate('manager', ['password' => 'valid']);
    }

    /**
     * @throws AccountsException
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
        $this->assertEquals(0, $saved->getClass());
    }

    /**
     * @throws AccountsException
     */
    public function testCreateAccountOnFailedHash(): void
    {
        $lib = $this->failedFileSources();
        $user = $this->wantedUser();

        // create
        $this->expectException(AccountsException::class);
        $lib->createAccount($user, 'here to set');
    }

    /**
     * @throws AccountsException
     */
    public function testUpdateAccountOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        $user = $this->wantedUser();

        // update
        $this->assertFalse($lib->updateAccount($user));
    }

    /**
     * @throws AccountsException
     */
    public function testUpdatePasswordOnEmptyInstance(): void
    {
        $lib = $this->emptyFileSources();
        // update
        $this->assertFalse($lib->updatePassword('This user does not exists', 'not important'));
    }

    /**
     * @throws AccountsException
     */
    public function testUpdatePasswordOnFailedHash(): void
    {
        $lib = $this->failedFileSources();
        // update
        $this->expectException(AccountsException::class);
        $lib->updatePassword('manager', 'not important');
    }

    /**
     * @throws AccountsException
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
    public function testAccountManipulation(): void
    {
        $lib = $this->fullFileSources();
        $user = $this->wantedUser();

        // create
        $lib->createAccount($user, 'here to set');
        // check data
        $saved = $lib->getDataOnly($user->getAuthName());
        $this->assertEquals('Testing another', $saved->getDisplayName());
        $this->assertEquals('why_here', $saved->getDir());
        $this->assertEquals(0, $saved->getClass());

        // check login
        $this->assertNotEmpty($lib->authenticate($user->getAuthName(), ['password' => 'here to set']));

        // update
        $usr = clone $user;
        $usr->setUserData(
            null,
            null,
            null,
            2,
            3,
            'WheĐn yoĐu dđo nođt knđow',
            null
        );
        $lib->updateAccount($usr);

        // check data - again with new values
        $saved = $lib->getDataOnly($user->getAuthName());
        $this->assertEquals('WheĐn yoĐu dđo nođt knđow', $saved->getDisplayName());
        $this->assertEquals(2, $saved->getClass());

        // update password
        $this->assertTrue($lib->updatePassword($user->getAuthName(), 'another pass'));
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
     */
    public function testCreateFail(): void
    {
        $lib = $this->fullFileSources();

        $user = $this->wantedUser();
        $user->setUserData(null, 'worker', null, null, null, null, null, null);
        $this->assertFalse($lib->createAccount($user, ''));

        $user = $this->wantedUser();
        $user->setUserData('1002', null, null, null, null, null, null, null);
        $this->assertFalse($lib->createAccount($user, ''));
    }

    /**
     * @throws AccountsException
     */
    public function testAllUsers(): void
    {
        $lib = $this->fullFileSources();
        $data = $lib->readAccounts();
        /** @var IUser[] $data */
        $this->assertEquals(1, $data[0]->getClass());
        $this->assertEquals('manager', $data[1]->getAuthName());
    }

    protected function wantedUser(): FileUser
    {
        $user = new FileUser();
        $user->setUserData('600', 'another', '0', 0, 2,'Testing another', 'why_here');
        return $user;
    }
}

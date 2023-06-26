<?php

namespace AccessTests;


use CommonTestClass;
use kalanis\kw_auth_sources\Access\CompositeSources;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Sources;
use kalanis\kw_locks\LockException;


class CompositeTest extends CommonTestClass
{
    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testBasic(): void
    {
        $acc = new Sources\Dummy\Accounts();
        $lib = new CompositeSources($acc, $acc, new Sources\Dummy\Groups(), new Sources\Classes());
        $this->assertInstanceOf(Sources\Dummy\Accounts::class, $lib->getAuth());
        $this->assertInstanceOf(Sources\Dummy\Accounts::class, $lib->getAccounts());
        $this->assertInstanceOf(Sources\Dummy\Groups::class, $lib->getGroups());
        $this->assertInstanceOf(Sources\Classes::class, $lib->getClasses());

        $this->assertNull($lib->authenticate('whatever'));
        $this->assertNull($lib->getDataOnly('whatever'));
        $this->assertFalse($lib->updateCertKeys('whatever', null, null));
        $this->assertNull($lib->getCertData('whatever'));

        $this->assertFalse($lib->createAccount(new \MockUser(), 'not important'));
        $this->assertEmpty($lib->readAccounts());
        $this->assertFalse($lib->updateAccount(new \MockUser()));
        $this->assertFalse($lib->updatePassword('whatever', 'not important'));
        $this->assertFalse($lib->deleteAccount('whatever'));

        $this->assertNotEmpty($lib->readClasses());

        $this->assertFalse($lib->createGroup(new \MockGroup()));
        $this->assertNull($lib->getGroupDataOnly('whatever'));
        $this->assertEmpty($lib->readGroup());
        $this->assertFalse($lib->updateGroup(new \MockGroup()));
        $this->assertFalse($lib->deleteGroup('whatever'));
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testCerts(): void
    {
        $acc = new Sources\Dummy\AccountsCerts();
        $lib = new CompositeSources($acc, $acc, new Sources\Dummy\Groups(), new Sources\Classes());
        $this->assertInstanceOf(Sources\Dummy\Accounts::class, $lib->getAuth());
        $this->assertInstanceOf(Sources\Dummy\AccountsCerts::class, $lib->getAccounts());

        $this->assertNull($lib->authenticate('whatever'));
        $this->assertNull($lib->getDataOnly('whatever'));
        $this->assertFalse($lib->updateCertKeys('whatever', null, null));
        $this->assertNull($lib->getCertData('whatever'));
    }
}

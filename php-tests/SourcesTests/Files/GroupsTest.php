<?php

namespace SourcesTests\Files;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Data\FileGroup;
use kalanis\kw_accounts\Interfaces\IGroup;
use kalanis\kw_locks\LockException;


class GroupsTest extends AFilesTest
{
    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testManipulation(): void
    {
        $lib = $this->fullGroupsSources();
        $group = $this->wantedGroup();

        // create
        $lib->createGroup($group);
        // check data
        $saved = $lib->getGroupDataOnly($group->getGroupId());
        $this->assertEquals('another', $saved->getGroupName());
        $this->assertEquals('Testing group', $saved->getGroupDesc());
        $this->assertEquals('1001', $saved->getGroupAuthorId());

        // update
        $group->setGroupData(
            null,
            null,
            'WheĐn yoĐu dđo nođt knđow',
            '1002',
            888
        );
        $lib->updateGroup($group);

        // check data - again with new values
        $saved = $lib->getGroupDataOnly($group->getGroupId());
        $this->assertEquals('When you do not know', $saved->getGroupDesc()); // overwrite this
        $this->assertEquals('1001', $saved->getGroupAuthorId()); // cannot overwrite this

        // remove
        $lib->deleteGroup($group->getGroupId());
        // check for existence
        $this->assertEmpty($lib->getGroupDataOnly($group->getGroupId()));
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testCreateFail(): void
    {
        $lib = $this->fullGroupsSources();
        $group = $this->wantedGroup('');
        $this->expectException(AccountsException::class);
        $lib->createGroup($group);
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testCreateNoFile(): void
    {
        $lib = $this->emptyGroupSources();
        $group = $this->wantedGroup('dummy');
        $this->assertTrue($lib->createGroup($group));
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testCreateLocked(): void
    {
        $lib = $this->lockedGroupsSources();
        $group = $this->wantedGroup();
        $this->expectException(AccountsException::class);
        $lib->createGroup($group);
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testGetDataNoFile(): void
    {
        $lib = $this->emptyGroupSources();
        $this->assertEmpty($lib->getGroupDataOnly('dummy'));
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testGetDataLocked(): void
    {
        $lib = $this->lockedGroupsSources();
        $this->expectException(AccountsException::class);
        $lib->getGroupDataOnly('dummy');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testUpdateLocked(): void
    {
        $lib = $this->lockedGroupsSources();
        $this->expectException(AccountsException::class);
        $lib->updateGroup($this->wantedGroup());
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testDeleteNoFile(): void
    {
        $lib = $this->emptyGroupSourcesWithFiles();
        $this->assertFalse($lib->deleteGroup('8'));
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testDeleteFail(): void
    {
        $lib = $this->fullGroupsSources();
        $this->expectException(AccountsException::class);
        $lib->deleteGroup('1');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testDeleteLocked(): void
    {
        $lib = $this->lockedGroupsSources();
        $this->expectException(AccountsException::class);
        $lib->deleteGroup('1');
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testAllGroups(): void
    {
        $lib = $this->fullGroupsSources();
        /** @var IGroup[] $data */
        $data = $lib->readGroup();
        $this->assertEquals('Maintainers', $data[0]->getGroupDesc());
        $this->assertEquals('1000', $data[1]->getGroupAuthorId());
    }

    /**
     * @throws AccountsException
     * @throws LockException
     */
    public function testAllGroupsLocked(): void
    {
        $lib = $this->lockedGroupsSources();
        $this->expectException(AccountsException::class);
        $lib->readGroup();
    }

    protected function wantedGroup($name = 'another'): FileGroup
    {
        $user = new FileGroup();
        $user->setGroupData('3', $name, 'Testing group', '1001', 999);
        return $user;
    }
}

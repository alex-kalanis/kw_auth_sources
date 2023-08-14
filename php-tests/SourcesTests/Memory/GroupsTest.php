<?php

namespace SourcesTests\Memory;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Data\FileGroup;
use kalanis\kw_auth_sources\Interfaces\IGroup;
use kalanis\kw_locks\LockException;


class GroupsTest extends AMemoryTest
{
    /**
     * @throws AuthSourcesException
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
        $grp = clone $group;
        $grp->setGroupData(
            $saved->getGroupId(),
            null,
            'When you do not know',
            '1002',
            888
        );
        $this->assertTrue($lib->updateGroup($grp));

        // check data - again with new values
        $saved2 = $lib->getGroupDataOnly($grp->getGroupId());
        $this->assertEquals('When you do not know', $saved2->getGroupDesc()); // overwrite this
        $this->assertEquals('1001', $saved2->getGroupAuthorId()); // cannot overwrite this

        // remove
        $lib->deleteGroup($group->getGroupId());
        // check for existence
        $this->assertEmpty($lib->getGroupDataOnly($group->getGroupId()));
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testCreateFail(): void
    {
        $lib = $this->fullGroupsSources();
        $group = $this->wantedGroup('', '2');
        $this->assertFalse($lib->createGroup($group));
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testUpdateNothingWhat(): void
    {
        $lib = $this->fullGroupsSources();
        $group = $this->wantedGroup();
        $this->assertFalse($lib->updateGroup($group));
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testGetDataNoFile(): void
    {
        $lib = $this->emptyGroupSources();
        $this->assertEmpty($lib->getGroupDataOnly('dummy'));
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testDeleteFail(): void
    {
        $lib = $this->fullGroupsSources();
        $this->assertFalse($lib->deleteGroup('unknown'));
    }

    /**
     * @throws AuthSourcesException
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

    protected function wantedGroup($name = 'another', $id = '3'): FileGroup
    {
        $user = new FileGroup();
        $user->setGroupData($id, $name, 'Testing group', '1001', 999);
        return $user;
    }
}

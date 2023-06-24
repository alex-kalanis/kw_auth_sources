<?php

namespace SourcesTests\Files;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Data\FileGroup;
use kalanis\kw_auth_sources\Interfaces\IGroup;
use kalanis\kw_locks\LockException;


class GroupsTest extends AFilesTest
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
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testCreateFail(): void
    {
        $lib = $this->fullGroupsSources();
        $group = $this->wantedGroup('');
        $this->expectException(AuthSourcesException::class);
        $lib->createGroup($group);
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testCreateNoFile(): void
    {
        $lib = $this->emptyGroupSources();
        $group = $this->wantedGroup('dummy');
        $this->assertTrue($lib->createGroup($group));
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
    public function testDeleteNoFile(): void
    {
        $lib = $this->emptyGroupSourcesWithFiles();
        $this->assertFalse($lib->deleteGroup('8'));
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testDeleteFail(): void
    {
        $lib = $this->fullGroupsSources();
        $this->expectException(AuthSourcesException::class);
        $lib->deleteGroup('1');
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

    protected function wantedGroup($name = 'another'): FileGroup
    {
        $user = new FileGroup();
        $user->setGroupData('3', $name, 'Testing group', '1001', 999);
        return $user;
    }
}

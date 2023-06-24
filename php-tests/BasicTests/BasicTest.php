<?php

namespace BasicTests;


use CommonTestClass;
use kalanis\kw_auth_sources\Data;


class BasicTest extends CommonTestClass
{
    public function testUser(): void
    {
        $user = new Data\FileUser();
        $this->assertEmpty($user->getAuthId());
        $this->assertEmpty($user->getAuthName());
        $this->assertEmpty($user->getGroup());
        $this->assertEmpty($user->getClass());
        $this->assertEmpty($user->getDisplayName());
        $this->assertEmpty($user->getDir());
        $user->setUserData('123', 'lkjh', '800', 900, 12, 'DsFh', 'noooone');
        $this->assertEquals('123', $user->getAuthId());
        $this->assertEquals('lkjh', $user->getAuthName());
        $this->assertEquals('800', $user->getGroup());
        $this->assertEquals(900, $user->getClass());
        $this->assertEquals(12, $user->getStatus());
        $this->assertEquals('DsFh', $user->getDisplayName());
        $this->assertEquals('noooone', $user->getDir());
        $user->setUserData(null, 'skdvgjb', '', null, null, 'habbx', null);
        $this->assertEquals('123', $user->getAuthId());
        $this->assertEquals('skdvgjb', $user->getAuthName());
        $this->assertEquals('', $user->getGroup());
        $this->assertEquals(900, $user->getClass());
        $this->assertEquals(12, $user->getStatus());
        $this->assertEquals('habbx', $user->getDisplayName());
        $this->assertEquals('noooone', $user->getDir());
    }

    public function testGroup(): void
    {
        $group = new Data\FileGroup();
        $this->assertEmpty($group->getGroupId());
        $this->assertEmpty($group->getGroupName());
        $this->assertEmpty($group->getGroupAuthorId());
        $this->assertEmpty($group->getGroupDesc());
        $this->assertEmpty($group->getGroupParents());
        $group->setGroupData('987', 'lkjh', 'watwat', '800', 5);
        $this->assertEquals('987', $group->getGroupId());
        $this->assertEquals('lkjh', $group->getGroupName());
        $this->assertEquals('800', $group->getGroupAuthorId());
        $this->assertEquals('watwat', $group->getGroupDesc());
        $this->assertEquals(5, $group->getGroupStatus());
        $group->setGroupData(null, 'tfcijn', null, '', null, ['951', '357']);
        $this->assertEquals('987', $group->getGroupId());
        $this->assertEquals('tfcijn', $group->getGroupName());
        $this->assertEquals('', $group->getGroupAuthorId());
        $this->assertEquals('watwat', $group->getGroupDesc());
        $this->assertEquals(5, $group->getGroupStatus());
        $this->assertEquals(['951', '357'], $group->getGroupParents());
    }

    public function testCertUser(): void
    {
        $cert = new Data\FileCertUser();
        $this->assertEmpty($cert->getPubKey());
        $this->assertEmpty($cert->getPubSalt());
        $cert->addCertInfo('asdfghjkl', 'once_none');
        $this->assertEquals('asdfghjkl', $cert->getPubKey());
        $this->assertEquals('once_none', $cert->getPubSalt());
    }
}

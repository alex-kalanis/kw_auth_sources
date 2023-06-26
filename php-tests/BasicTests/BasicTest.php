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
        $this->assertEmpty($user->getExtra());
        $user->setUserData('123', 'lkjh', '800', 900, 12, 'DsFh', 'noooone', ['abc' => 'foo', 'def' => 'bar']);
        $this->assertEquals('123', $user->getAuthId());
        $this->assertEquals('lkjh', $user->getAuthName());
        $this->assertEquals('800', $user->getGroup());
        $this->assertEquals(900, $user->getClass());
        $this->assertEquals(12, $user->getStatus());
        $this->assertEquals('DsFh', $user->getDisplayName());
        $this->assertEquals('noooone', $user->getDir());
        $this->assertEquals(['abc' => 'foo', 'def' => 'bar'], $user->getExtra());
        $user->setUserData(null, 'skdvgjb', '', null, null, 'habbx', null, ['baz' => 'foo', 'def' => 'baz']);
        $this->assertEquals('123', $user->getAuthId());
        $this->assertEquals('skdvgjb', $user->getAuthName());
        $this->assertEquals('', $user->getGroup());
        $this->assertEquals(900, $user->getClass());
        $this->assertEquals(12, $user->getStatus());
        $this->assertEquals('habbx', $user->getDisplayName());
        $this->assertEquals('noooone', $user->getDir());
        $this->assertEquals(['abc' => 'foo', 'def' => 'baz', 'baz' => 'foo'], $user->getExtra());
    }

    public function testGroup(): void
    {
        $group = new Data\FileGroup();
        $this->assertEmpty($group->getGroupId());
        $this->assertEmpty($group->getGroupName());
        $this->assertEmpty($group->getGroupAuthorId());
        $this->assertEmpty($group->getGroupDesc());
        $this->assertEmpty($group->getGroupParents());
        $this->assertEmpty($group->getGroupExtra());
        $group->setGroupData('987', 'lkjh', 'watwat', '800', 5, [], ['abc' => 'foo', 'def' => 'bar']);
        $this->assertEquals('987', $group->getGroupId());
        $this->assertEquals('lkjh', $group->getGroupName());
        $this->assertEquals('800', $group->getGroupAuthorId());
        $this->assertEquals('watwat', $group->getGroupDesc());
        $this->assertEquals(5, $group->getGroupStatus());
        $this->assertEquals(['abc' => 'foo', 'def' => 'bar'], $group->getGroupExtra());
        $group->setGroupData(null, 'tfcijn', null, '', null, ['951', '357'], ['baz' => 'foo', 'def' => 'baz']);
        $this->assertEquals('987', $group->getGroupId());
        $this->assertEquals('tfcijn', $group->getGroupName());
        $this->assertEquals('', $group->getGroupAuthorId());
        $this->assertEquals('watwat', $group->getGroupDesc());
        $this->assertEquals(5, $group->getGroupStatus());
        $this->assertEquals(['951', '357'], $group->getGroupParents());
        $this->assertEquals(['abc' => 'foo', 'def' => 'baz', 'baz' => 'foo'], $group->getGroupExtra());
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

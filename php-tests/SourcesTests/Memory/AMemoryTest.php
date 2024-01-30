<?php

namespace SourcesTests\Memory;


use CommonTestClass;
use kalanis\kw_accounts\Data\FileCertUser;
use kalanis\kw_accounts\Data\FileGroup;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces\IHashes;
use kalanis\kw_auth_sources\Sources\Memory;
use MockHashes;


abstract class AMemoryTest extends CommonTestClass
{
    /**
     * Contains a full comedy/tragedy of work with locks
     * @return Memory\Groups
     */
    protected function fullGroupsSources(): Memory\Groups
    {
        return new Memory\Groups($this->getGroupClasses());
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @return Memory\Groups
     */
    protected function emptyGroupSources(): Memory\Groups
    {
        return new Memory\Groups();
    }

    /**
     * Contains a partial files - no groups or shadow files
     * @return Memory\Accounts
     */
    protected function fullFileSources(): Memory\Accounts
    {
        return new Memory\Accounts(new \MockHashes(), $this->getUserClasses());
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @return Memory\Accounts
     */
    protected function emptyFileSources(): Memory\Accounts
    {
        return new Memory\Accounts(new MockHashes());
    }

    /**
     * Contains a hash class which fails every time
     * @return Memory\Accounts
     */
    protected function failedFileSources(): Memory\Accounts
    {
        return new Memory\Accounts(new XHashFail(), $this->getUserClasses());
    }

    /**
     * Contains a partial files - no groups or shadow files
     * @return Memory\AccountsCerts
     */
    protected function fullCertFileSources(): Memory\AccountsCerts
    {
        return new Memory\AccountsCerts(new \MockHashes(), $this->getUserClasses());
    }

    /**
     * Contains a full comedy/tragedy of work with locks
     * @return Memory\AccountsCerts
     */
    protected function emptyCertFileSources(): Memory\AccountsCerts
    {
        return new Memory\AccountsCerts(new MockHashes());
    }

    protected function getGroupClasses(): array
    {
        $g1 = new FileGroup();
        $g1->setGroupData('0', 'root', 'Maintainers', '1000', 1);
        $g2 = new FileGroup();
        $g2->setGroupData('1', 'admin', 'Administrators', '1000', 1, [], ["hint" => "Sssh", "age" => 39, "powers" => ["foo","bar","baz"]]);
        $g3 = new FileGroup();
        $g3->setGroupData('2', 'user', 'All users', '1000', 1);
        return [$g1, $g2, $g3];
    }

    protected function getUserClasses(): array
    {
        $g1 = new FileCertUser();
        $g1->setUserData('1000', 'owner', '0', 1, 1, 'Owner', '/data/');
        $g2 = new FileCertUser();
        $g2->setUserData('1001', 'manager', '1', 2, 1, 'Manage', '/data/', ["hint" => "Uncut", "age" => 39, "powers" => ["foo","bar","baz"]]);
        $g3 = new FileCertUser();
        $g3->setUserData('1002', 'worker', '1', 3, 1, 'Worker', '/data/');
        $g3->updateCertInfo('donna', 'erch');
        return [$g1, $g2, $g3];
    }
}


class XHashFail implements IHashes
{
    public function checkHash(string $pass, string $hash): bool
    {
        throw new AuthSourcesException('mock');
    }

    public function createHash(string $pass, ?string $method = null): string
    {
        throw new AuthSourcesException('mock');
    }
}


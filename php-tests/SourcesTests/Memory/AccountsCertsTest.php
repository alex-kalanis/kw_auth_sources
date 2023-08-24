<?php

namespace SourcesTests\Memory;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_locks\LockException;


class AccountsCertsTest extends AMemoryTest
{
    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testCertData(): void
    {
        $lib = $this->fullCertFileSources();
        $this->assertNull($lib->getCertData('not exists'));

        $data = $lib->getCertData('worker');
        $this->assertEquals('donna', $data->getPubKey());
        $this->assertEquals('erch', $data->getPubSalt());
    }

    /**
     * @throws AuthSourcesException
     * @throws LockException
     */
    public function testUpdateCert(): void
    {
        $lib = $this->fullCertFileSources();
        $this->assertTrue($lib->updateCertKeys('manager', 'foo', 'bar'));

        $data = $lib->getCertData('manager');
        $this->assertNotNull($data);
        $this->assertEquals('foo', $data->getPubKey());
        $this->assertEquals('bar', $data->getPubSalt());

        $this->assertFalse($lib->updateCertKeys('not-exists', 'foo', 'bar'));
    }
}

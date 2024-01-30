<?php

namespace SourcesTests\Memory;


use kalanis\kw_accounts\AccountsException;


class AccountsCertsTest extends AMemoryTest
{
    /**
     * @throws AccountsException
     */
    public function testCertData(): void
    {
        $lib = $this->fullCertFileSources();
        $this->assertNull($lib->getCertData('not exists'));

        $data = $lib->getCertData('worker');
        $this->assertEquals('donna', $data->getPubKey());
        $this->assertEquals('erch', $data->getSalt());
    }

    /**
     * @throws AccountsException
     */
    public function testUpdateCert(): void
    {
        $lib = $this->fullCertFileSources();
        $this->assertTrue($lib->updateCertData('manager', 'foo', 'bar'));

        $data = $lib->getCertData('manager');
        $this->assertNotNull($data);
        $this->assertEquals('foo', $data->getPubKey());
        $this->assertEquals('bar', $data->getSalt());

        $this->assertFalse($lib->updateCertData('not-exists', 'foo', 'bar'));
    }
}

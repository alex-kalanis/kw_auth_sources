<?php

namespace BasicTests;


use CommonTestClass;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Hashes;


class HashesTest extends CommonTestClass
{
    /**
     * @param string $what
     * @throws AuthSourcesException
     * @dataProvider passwordsProvider
     */
    public function testOriginal(string $what): void
    {
        $lib = new Hashes\KwOrig('asdfghkl123qweqrtziop456yxcvbnm789');
        $this->assertTrue($lib->checkHash($what, $lib->createHash($what)));
    }

    /**
     * @param string $what
     * @throws AuthSourcesException
     * @dataProvider passwordsProvider
     */
    public function testMd5(string $what): void
    {
        $lib = new Hashes\Md5();
        $this->assertTrue($lib->checkHash($what, $lib->createHash($what)));
    }

    /**
     * @param string $what
     * @throws AuthSourcesException
     * @dataProvider passwordsProvider
     */
    public function testCore(string $what): void
    {
        $lib = new Hashes\CoreLib();
        $this->assertTrue($lib->checkHash($what, $lib->createHash($what)));
    }

    public function passwordsProvider(): array
    {
        return [
            ['okmijnuhb', ],
            ['wsxedcrfv', ],
        ];
    }
}

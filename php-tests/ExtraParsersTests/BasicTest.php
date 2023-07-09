<?php

namespace ExtraParsersTests;


use CommonTestClass;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\ExtraParsers;


class BasicTest extends CommonTestClass
{
    /**
     * @param array<string|int, string|int|float|bool> $data
     * @throws AuthSourcesException
     * @dataProvider compactProvider
     */
    public function testJson($data): void
    {
        $lib = new ExtraParsers\Json();
        $this->assertEquals($data, $lib->expand($lib->compact($data)));
    }

    /**
     * @param array<string|int, string|int|float|bool> $data
     * @throws AuthSourcesException
     * @dataProvider compactProvider
     */
    public function testSerial($data): void
    {
        $lib = new ExtraParsers\Serialize();
        $this->assertEquals($data, $lib->expand($lib->compact($data)));
    }

    /**
     * @throws AuthSourcesException
     */
    public function testNone(): void
    {
        $lib = new ExtraParsers\None();
        $this->assertEquals([], $lib->expand('something'));
        $this->assertEquals('', $lib->compact(['something']));
    }

    public function compactProvider(): array
    {
        return [
            [['ser' => 'a', 123 => 456, 'wat' => false, 951 => 154.7557]]
        ];
    }
}

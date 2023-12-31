<?php

namespace TraitsTests;


use CommonTestClass;
use kalanis\kw_auth_sources\Traits;


class StatusTest extends CommonTestClass
{
    /**
     * @param int|null $what
     * @dataProvider statusesProvider
     */
    public function testOriginal(?int $what): void
    {
        $lib = new XStatusTransform();
        $this->assertEquals($what, $lib->to($lib->from($what)));
    }

    public function statusesProvider(): array
    {
        return [
            [1, ],
            [22, ],
            [null, ],
        ];
    }

    public function testList(): void
    {
        $lib = new XStatusList();
        $this->assertNotEmpty($lib->readUserStatuses());
    }
}


class XStatusTransform
{
    use Traits\TStatusTransform;

    public function from(?int $value): string
    {
        return $this->transformFromIntToString($value);
    }

    public function to(string $value): ?int
    {
        return $this->transformFromStringToInt($value);
    }
}


class XStatusList
{
    use Traits\TUserStatuses;
}

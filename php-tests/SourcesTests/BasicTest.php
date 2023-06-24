<?php

namespace SourcesTests;


use CommonTestClass;
use kalanis\kw_auth_sources\Sources;


class BasicTest extends CommonTestClass
{
    public function testClasses(): void
    {
        $lib = new Sources\Classes();
        $data = $lib->readClasses();
        $this->assertEquals('Maintainer', $data[1]);
        $this->assertEquals('User', $data[3]);
    }
}

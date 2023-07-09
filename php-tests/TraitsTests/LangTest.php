<?php

namespace TraitsTests;


use CommonTestClass;
use kalanis\kw_auth_sources\Interfaces\IKAusTranslations;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_auth_sources\Translations;


class LangTest extends CommonTestClass
{
    public function testSimple(): void
    {
        $lib = new XLang();
        $this->assertNotEmpty($lib->getAusLang());
        $this->assertInstanceOf(Translations::class, $lib->getAusLang());
        $lib->setAusLang(new XTrans());
        $this->assertInstanceOf(XTrans::class, $lib->getAusLang());
        $lib->setAusLang(null);
        $this->assertInstanceOf(Translations::class, $lib->getAusLang());
    }
}


class XLang
{
    use TLang;
}


class XTrans implements IKAusTranslations
{
    public function kauPassFileNotFound(string $path): string
    {
        return 'mock';
    }

    public function kauPassMustBeSet(): string
    {
        return 'mock';
    }

    public function kauPassMissParam(): string
    {
        return 'mock';
    }

    public function kauPassLoginExists(): string
    {
        return 'mock';
    }

    public function kauLockSystemNotSet(): string
    {
        return 'mock';
    }

    public function kauAuthAlreadyOpen(): string
    {
        return 'mock';
    }

    public function kauGroupMissParam(): string
    {
        return 'mock';
    }

    public function kauGroupHasMembers(): string
    {
        return 'mock';
    }

    public function kauHashFunctionNotFound(): string
    {
        return 'mock';
    }

    public function kauCombinationUnavailable(): string
    {
        return 'mock';
    }

    public function kauNoDelimiterSet(): string
    {
        return 'mock';
    }
}
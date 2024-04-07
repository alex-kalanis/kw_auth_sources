<?php

namespace kalanis\kw_auth_sources\Interfaces;


/**
 * Interface IKAusTranslations
 * @package kalanis\kw_auth_sources\Interfaces
 * Translations
 */
interface IKAusTranslations
{
    public function kauPassFileNotFound(string $path): string;

    public function kauPassMustBeSet(): string;

    public function kauPassMissParam(): string;

    public function kauPassLoginExists(): string;

    public function kauLockSystemNotSet(): string;

    public function kauAuthAlreadyOpen(): string;

    public function kauGroupMissParam(): string;

    public function kauGroupHasMembers(): string;

    public function kauHashFunctionNotFound(): string;

    public function kauCombinationUnavailable(): string;

    public function kauNoDelimiterSet(): string;

    public function kauGroupMissAuth(): string;

    public function kauGroupMissAccounts(): string;

    public function kauGroupMissClasses(): string;

    public function kauGroupMissGroups(): string;
}

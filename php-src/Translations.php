<?php

namespace kalanis\kw_auth_sources;


use kalanis\kw_auth_sources\Interfaces\IKAusTranslations;


/**
 * Class Translations
 * @package kalanis\kw_auth_sources
 */
class Translations implements IKAusTranslations
{
    public function kauPassFileNotFound(string $path): string
    {
        return 'File with passwords not found in preselect path';
    }

    public function kauPassMustBeSet(): string
    {
        return 'You must set the password to check!';
    }

    public function kauPassMissParam(): string
    {
        return 'Missing necessary params to determine the password';
    }

    public function kauPassLoginExists(): string
    {
        return 'Login name already exists!';
    }

    public function kauLockSystemNotSet(): string
    {
        return 'Lock system not set';
    }

    public function kauAuthAlreadyOpen(): string
    {
        return 'Someone works with authentication. Please try again a bit later.';
    }

    public function kauGroupMissParam(): string
    {
        return 'Missing necessary params to determine the group';
    }

    public function kauGroupHasMembers(): string
    {
        return 'Group to removal still has members. Remove them first.';
    }

    /**
     * @return string
     * @codeCoverageIgnore only on really specific installations
     */
    public function kauHashFunctionNotFound(): string
    {
        return 'Cannot find function for making hashes!';
    }

    public function kauCombinationUnavailable(): string
    {
        return 'This combination is not available!';
    }

    /**
     * @return string
     * @codeCoverageIgnore only on really specific installations
     */
    public function kauNoDelimiterSet(): string
    {
        return 'No delimiter set in auth files!';
    }

    public function kauGroupMissAuth(): string
    {
        return 'Class which manipulates the authentication is not set!';
    }

    public function kauGroupMissAccounts(): string
    {
        return 'Class which manipulates the accounts itselves is not set!';
    }

    public function kauGroupMissClasses(): string
    {
        return 'Class which manipulates the user classes is not set!';
    }

    public function kauGroupMissGroups(): string
    {
        return 'Class which manipulates the user groups is not set!';
    }
}

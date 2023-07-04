<?php

namespace kalanis\kw_auth_sources\Interfaces;


use kalanis\kw_auth_sources\AuthSourcesException;


/**
 * Interface IExtraParser
 * @package kalanis\kw_auth_sources\Interfaces
 * Parsing extra data from and to string in storage
 */
interface IExtraParser
{
    /**
     * Make string from data to store in that storage
     * @param array<string|int, string|int|float|bool> $data
     * @throws AuthSourcesException
     * @return string
     */
    public function compact(array $data): string;

    /**
     * Make original resource structure from stored data
     * @param string $data
     * @throws AuthSourcesException
     * @return array<string|int, string|int|float|bool>
     */
    public function expand(string $data): array;
}

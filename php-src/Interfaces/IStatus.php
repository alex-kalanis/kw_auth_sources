<?php

namespace kalanis\kw_auth_sources\Interfaces;


/**
 * Interface IStatus
 * @package kalanis\kw_auth_sources\Interfaces
 * Status of account - allow action
 */
interface IStatus
{
    /**
     * @param int|null $status
     * @return bool
     */
    public function allowLogin(?int $status): bool;

    /**
     * @param int|null $status
     * @return bool
     */
    public function allowCert(?int $status): bool;
}

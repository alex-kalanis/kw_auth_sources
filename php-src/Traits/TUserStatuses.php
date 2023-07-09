<?php

namespace kalanis\kw_auth_sources\Traits;


use kalanis\kw_auth_sources\Interfaces\IUser;


/**
 * Trait TUserStatuses
 * @package kalanis\kw_auth_sources\Traits
 * Authenticate via files - manage user statuses
 */
trait TUserStatuses
{
    /**
     * @return string[]
     */
    public function readUserStatuses(): array
    {
        return [
            IUser::USER_STATUS_UNKNOWN => 'Unknown',
            IUser::USER_STATUS_DISABLED => 'Disabled',
            IUser::USER_STATUS_ENABLED => 'Enabled',
            IUser::USER_STATUS_ONLY_LOGIN => 'Only via login',
            IUser::USER_STATUS_ONLY_CERT => 'Only via certs',
        ];
    }
}

<?php

namespace kalanis\kw_auth_sources\Statuses;


use kalanis\kw_accounts\Interfaces as acc_interfaces;
use kalanis\kw_auth_sources\Interfaces;


/**
 * Class Checked
 * @package kalanis\kw_auth_sources\Statuses
 * Authenticate - is allowed to use any available method?
 */
class Checked implements Interfaces\IStatus
{
    public function allowLogin(?int $status): bool
    {
        return is_int($status) && in_array($status, [
            acc_interfaces\IUser::USER_STATUS_ENABLED,
            acc_interfaces\IUser::USER_STATUS_ONLY_LOGIN,
        ]);
    }

    public function allowCert(?int $status): bool
    {
        return is_int($status) && in_array($status, [
            acc_interfaces\IUser::USER_STATUS_ENABLED,
            acc_interfaces\IUser::USER_STATUS_ONLY_CERT,
        ]);
    }
}

<?php

namespace kalanis\kw_auth_sources\Traits;


use kalanis\kw_accounts\Interfaces\IUser;


/**
 * Trait TStatusTransform
 * @package kalanis\kw_auth_sources\Traits
 * Status - integer to string and back
 */
trait TStatusTransform
{
    protected function transformFromIntToString(?int $value): string
    {
        return is_null($value) ? IUser::STATUS_NONE : strval($value);
    }

    protected function transformFromStringToInt(string $value): ?int
    {
        return IUser::STATUS_NONE == $value ? null : intval($value);
    }
}

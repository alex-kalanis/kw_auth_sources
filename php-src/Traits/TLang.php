<?php

namespace kalanis\kw_auth_sources\Traits;


use kalanis\kw_auth_sources\Interfaces\IKAusTranslations;
use kalanis\kw_auth_sources\Translations;


/**
 * Trait TLang
 * @package kalanis\kw_auth_sources\Traits
 */
trait TLang
{
    protected ?IKAusTranslations $ausLang = null;

    public function setAusLang(?IKAusTranslations $ausLang = null): void
    {
        $this->ausLang = $ausLang;
    }

    public function getAusLang(): IKAusTranslations
    {
        if (empty($this->ausLang)) {
            $this->ausLang = new Translations();
        }
        return $this->ausLang;
    }
}

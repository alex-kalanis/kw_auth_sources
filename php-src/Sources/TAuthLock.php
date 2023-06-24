<?php

namespace kalanis\kw_auth_sources\Sources;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Traits\TLang;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Trait TAuthLock
 * @package kalanis\kw_auth_sources\Sources
 */
trait TAuthLock
{
    use TLang;

    /** @var ILock|null */
    protected $lock = null;

    protected function initAuthLock(?ILock $lock): void
    {
        $this->lock = $lock;
    }

    /**
     * @return ILock
     * @throws AuthSourcesException
     */
    protected function getLock(): ILock
    {
        if (!$this->lock) {
            throw new AuthSourcesException($this->getAusLang()->kauLockSystemNotSet());
        }
        return $this->lock;
    }

    /**
     * @param string $note
     * @throws AuthSourcesException
     * @throws LockException
     */
    protected function checkLock(string $note = ''): void
    {
        if ($this->getLock()->has()) {
            throw new AuthSourcesException(empty($note) ? $this->getAusLang()->kauAuthAlreadyOpen() : $note);
        }
    }
}

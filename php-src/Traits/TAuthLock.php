<?php

namespace kalanis\kw_auth_sources\Traits;


use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Trait TAuthLock
 * @package kalanis\kw_auth_sources\Traits
 */
trait TAuthLock
{
    use TLang;

    protected ?ILock $lock = null;

    protected function initAuthLock(?ILock $lock): void
    {
        $this->lock = $lock;
    }

    /**
     * @throws AuthSourcesException
     * @return ILock
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

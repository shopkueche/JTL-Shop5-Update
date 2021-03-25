<?php

namespace JTL\Session\Handler;

use JTL\Shop;

/**
 * Class Bot
 * @package JTL\Session\Handler
 */
class Bot extends JTLDefault
{
    /**
     * @var string
     */
    protected $sessionID = '';

    /**
     * @var bool
     */
    private $doSave;

    /**
     * @param bool $doSave - when true, session is saved, otherwise it will be discarded immediately
     */
    public function __construct($doSave = false)
    {
        $this->sessionID = \session_id();
        $this->doSave    = $doSave;
    }

    /**
     * @inheritDoc
     */
    public function open($path, $name)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read($id)
    {
        $sessionData = '';
        if ($this->doSave === true) {
            $sessionData = (($sessionData = Shop::Container()->getCache()->get($this->sessionID)) !== false)
                ? $sessionData
                : '';
        }

        return $sessionData;
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data)
    {
        if ($this->doSave === true) {
            Shop::Container()->getCache()->set($this->sessionID, $data, [\CACHING_GROUP_CORE]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy($id)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc($max_lifetime)
    {
        return true;
    }
}

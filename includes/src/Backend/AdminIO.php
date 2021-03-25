<?php

namespace JTL\Backend;

use JTL\IO\IO;
use JTL\IO\IOError;

/**
 * Class AdminIO
 * @package JTL\Backend
 */
class AdminIO extends IO
{
    /**
     * @var AdminAccount
     */
    protected $oAccount;

    /**
     * @param AdminAccount $account
     * @return $this
     */
    public function setAccount(AdminAccount $account): self
    {
        $this->oAccount = $account;

        return $this;
    }

    /**
     * @return AdminAccount|null
     */
    public function getAccount(): ?AdminAccount
    {
        return $this->oAccount;
    }

    /**
     * @param string $name
     * @param null $function
     * @param null $include
     * @param null $permission
     * @return $this
     * @throws \Exception
     */
    public function register(string $name, $function = null, $include = null, $permission = null)
    {
        parent::register($name, $function, $include);
        $this->functions[$name][] = $permission;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $params
     * @return mixed
     * @throws \Exception
     */
    public function execute(string $name, $params)
    {
        if (!$this->exists($name)) {
            return new IOError('Function not registered');
        }

        $permission = $this->functions[$name][2];

        if ($permission !== null && !$this->oAccount->permission($permission)) {
            return new IOError('User has not the required permission to execute this function', 401);
        }

        return parent::execute($name, $params);
    }
}

<?php

namespace Housekeeper\Flows;

use Housekeeper\Contracts\Action;
use Housekeeper\Contracts\Repository;
use Housekeeper\Contracts\Flow\Before as BeforeContract;

/**
 * Class Before
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Flow
 */
class Before implements BeforeContract
{
    /**
     * @var Action
     */
    protected $action;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var mixed
     */
    protected $returnValue;


    /**
     * @param \Housekeeper\Contracts\Repository $repository
     * @param \Housekeeper\Contracts\Action     $action
     */
    public function __construct(Repository $repository, Action $action)
    {
        $this->repository = $repository;
        $this->action     = $action;
    }

    /**
     * @return \Housekeeper\Contracts\Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return \Housekeeper\Contracts\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return bool
     */
    public function hasReturnValue()
    {
        return ! is_null($this->returnValue);
    }

    /**
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * @param mixed $value
     */
    public function setReturnValue($value)
    {
        $this->returnValue = $value;
    }
}
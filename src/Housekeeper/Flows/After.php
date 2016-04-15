<?php

namespace Housekeeper\Flows;

use Housekeeper\Contracts\Action;
use Housekeeper\Contracts\Repository;
use Housekeeper\Contracts\Flow\After as AfterContract;

/**
 * Class After
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Flow
 */
class After implements AfterContract
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
     * @var int
     */
    protected $index;

    /**
     * @var mixed
     */
    protected $returnValue;


    /**
     * @param \Housekeeper\Contracts\Repository $repository
     * @param \Housekeeper\Contracts\Action     $action
     * @param int                               $index
     * @param mixed                             $returnValue
     */
    public function __construct(Repository $repository, Action $action, $index, $returnValue)
    {
        $this->repository  = $repository;
        $this->action      = $action;
        $this->index       = $index;
        $this->returnValue = $returnValue;
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
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
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
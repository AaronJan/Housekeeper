<?php

namespace Housekeeper\Flows;

use Housekeeper\Action;
use Housekeeper\Contracts\Flow\Basic as FlowContract;

/**
 * Class Reset
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Flow
 */
class Reset implements FlowContract
{
    /**
     * @var Action
     */
    protected $action;

    /**
     * @var
     */
    protected $repository;


    /**
     * @param        $repository
     * @param Action $action
     */
    public function __construct($repository, Action $action)
    {
        $this->repository = $repository;
        $this->action     = $action;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }

}
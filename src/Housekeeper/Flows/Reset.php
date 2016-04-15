<?php

namespace Housekeeper\Flows;

use Housekeeper\Contracts\Repository;
use Housekeeper\Contracts\Action;
use Housekeeper\Contracts\Flow\Reset as ResetContract;

/**
 * Class Reset
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Flow
 */
class Reset implements ResetContract
{
    /**
     * @var \Housekeeper\Contracts\Action
     */
    protected $action;

    /**
     * @var \Housekeeper\Contracts\Repository
     */
    protected $repository;

    /**
     * @var int
     */
    protected $index;


    /**
     * @param \Housekeeper\Contracts\Repository $repository
     * @param \Housekeeper\Contracts\Action     $action
     * @param int                               $index
     */
    public function __construct(Repository $repository, Action $action, $index)
    {
        $this->repository = $repository;
        $this->action     = $action;
        $this->index      = $index;
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
}
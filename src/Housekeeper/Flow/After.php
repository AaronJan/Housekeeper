<?php namespace Housekeeper\Flow;

use Housekeeper\Action;
use Housekeeper\Contracts\RepositoryInterface;
use Housekeeper\Contracts\Flow\Basic as FlowContract;

/**
 * Class After
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Flow
 */
class After implements FlowContract
{

    /**
     * @var Action
     */
    protected $action;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var
     */
    protected $returnValue;


    /**
     * @param RepositoryInterface $repository
     * @param Action              $action
     * @param                     $returnValue
     */
    public function __construct(RepositoryInterface $repository, Action $action, $returnValue)
    {
        $this->repository  = $repository;
        $this->action      = $action;
        $this->returnValue = $returnValue;
    }

    /**
     * @return Action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->returnValue;
    }

    /**
     * @param $value
     */
    public function setReturn($value)
    {
        $this->returnValue = $value;
    }

}
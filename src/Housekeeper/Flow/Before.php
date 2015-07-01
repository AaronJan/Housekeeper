<?php namespace Housekeeper\Flow;

use Housekeeper\Action;
use Housekeeper\Contracts\RepositoryInterface;
use Housekeeper\Contracts\Flow\Basic as FlowContract;

/**
 * Class Before
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Flow
 */
class Before implements FlowContract
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
     */
    public function __construct(RepositoryInterface $repository, Action $action)
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
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return bool
     */
    public function hasReturn()
    {
        return ! is_null($this->returnValue);
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
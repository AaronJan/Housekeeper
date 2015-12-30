<?php

namespace Housekeeper;

/**
 * Class Action
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper
 */
class Action implements Contracts\Action
{
    /**
     * @var int
     */
    const UNKNOW = - 1;

    /**
     * @var int
     */
    const CREATE = 1;

    /**
     * @var int
     */
    const UPDATE = 2;

    /**
     * @var int
     */
    const READ = 3;

    /**
     * @var int
     */
    const DELETE = 4;

    /**
     * @var int
     */
    const CREATE_OR_UPDATE = 5;

    /**
     * @var int
     */
    const INTERNAL = 6;

    /**
     * @var int
     */
    const IGNORED = 7;

    /**
     * @var integer
     */
    protected $type;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var string
     */
    protected $methodName;


    /**
     * Action constructor.
     *
     * @param string   $methodName
     * @param array    $arguments
     * @param null|int $type
     */
    public function __construct($methodName, array $arguments, $type = null)
    {
        $this->methodName = $methodName;
        $this->arguments  = $arguments;
        $this->type       = is_null($type) ? static::UNKNOW : $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return bool
     */
    public function isType($type)
    {
        return $this->type === $type;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }
}
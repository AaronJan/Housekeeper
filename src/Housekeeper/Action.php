<?php namespace Housekeeper;

/**
 * Class Action
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper
 */
class Action
{

    /**
     * Flag action as "Unknow" action.
     */
    const UNKNOW = -1;

    /**
     * Flag action as "Create" action.
     */
    const CREATE = 1;

    /**
     * Flag action as "Update" action.
     */
    const UPDATE = 2;

    /**
     * Flag action as "Read" action.
     */
    const READ = 3;

    /**
     * Flag action as "Delete" action.
     */
    const DELETE = 4;

    /**
     * @var integer
     */
    protected $type;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var
     */
    protected $methodName;


    /**
     * @param       $methodName
     * @param array $arguments
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
     * @param integer $type
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
     * @return mixed
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return bool
     */
    public function isUnknow()
    {
        return $this->type == static::UNKNOW;
    }

    /**
     * @return bool
     */
    public function isCreate()
    {
        return $this->type == static::CREATE;
    }

    /**
     * @return bool
     */
    public function isUpdate()
    {
        return $this->type == static::UPDATE;
    }

    /**
     * @return bool
     */
    public function isRead()
    {
        return $this->type == static::READ;
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->type == static::DELETE;
    }

}
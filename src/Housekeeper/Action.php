<?php

namespace Housekeeper;

use Housekeeper\Contracts\Action as ActionContract;

/**
 * Class Action
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper
 */
class Action implements ActionContract
{
    /**
     * @var integer
     */
    protected $type;

    /**
     * @var null|array
     */
    protected $arguments;

    /**
     * @var null|string
     */
    protected $methodName;

    /**
     * @var array
     */
    protected $discriptions;

    /**
     * Action constructor.
     *
     * @param int        $type
     * @param array      $discriptions
     * @param null       $methodName
     * @param array|null $arguments
     */
    public function __construct($type = ActionContract::UNKNOW,
                                array $discriptions = [],
                                $methodName = null,
                                array $arguments = null)
    {
        $this->type         = is_null($type) ? static::UNKNOW : $type;
        $this->discriptions = $discriptions;

        if ($methodName) {
            $this->setMethodName($methodName);
        }

        if (! is_null($arguments)) {
            $this->setArguments($arguments);
        }
    }

    /**
     * @param string $methodName
     */
    public function setMethodName($methodName)
    {
        $this->methodName = $methodName;
    }

    /**
     * @return null|string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return array|null
     */
    public function getArguments()
    {
        return $this->arguments;
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
     * @param $discription
     */
    public function describeAs($discription)
    {
        $this->discriptions[] = $discription;
    }

    /**
     * @param $discription
     * @return bool
     */
    public function isDescribedAs($discription)
    {
        return in_array($discription, $this->discriptions);
    }
}
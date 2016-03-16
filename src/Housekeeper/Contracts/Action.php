<?php

namespace Housekeeper\Contracts;

/**
 * Interface Action
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts
 */
interface Action
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
     * @param string $methodName
     */
    public function setMethodName($methodName);

    /**
     * @return null|string
     */
    public function getMethodName();

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments);

    /**
     * @return null|array
     */
    public function getArguments();

    /**
     * @return int
     */
    public function getType();

    /**
     * @param int $type
     * @return bool
     */
    public function isType($type);

    /**
     * @param $discription
     */
    public function describeAs($discription);

    /**
     * @param $discription
     * @return bool
     */
    public function isDescribedAs($discription);
}
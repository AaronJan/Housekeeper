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
     * @return int
     */
    public function getType();

    /**
     * @param int $type
     * @return bool
     */
    public function isType($type);

    /**
     * @return array
     */
    public function getArguments();

    /**
     * @return string
     */
    public function getMethodName();
}
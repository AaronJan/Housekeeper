<?php

namespace Housekeeper\Contracts\Flow;

use Housekeeper\Contracts\Repository;
use Housekeeper\Contracts\Action;

/**
 * Interface After
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Flow
 */
interface After extends Basic
{
    /**
     * @param \Housekeeper\Contracts\Repository $repository
     * @param \Housekeeper\Contracts\Action     $action
     * @param mixed                             $returnValue
     */
    public function __construct(Repository $repository, Action $action, $returnValue);

    /**
     * @return mixed
     */
    public function getReturnValue();

    /**
     * @param mixed $value
     * @return void
     */
    public function setReturnValue($value);
}
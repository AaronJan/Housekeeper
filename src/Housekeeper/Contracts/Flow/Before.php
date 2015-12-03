<?php

namespace Housekeeper\Contracts\Flow;

use Housekeeper\Contracts\Action;
use Housekeeper\Contracts\Repository;

/**
 * Interface Before
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Flow
 */
interface Before extends Basic
{
    /**
     * @param \Housekeeper\Contracts\Repository $repository
     * @param \Housekeeper\Contracts\Action     $action
     */
    public function __construct(Repository $repository, Action $action);

    /**
     * @return bool
     */
    public function hasReturnValue();

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
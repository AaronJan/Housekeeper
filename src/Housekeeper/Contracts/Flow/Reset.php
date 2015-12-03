<?php

namespace Housekeeper\Contracts\Flow;

use Housekeeper\Contracts\Repository;
use Housekeeper\Contracts\Action;

/**
 * Interface Reset
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Flow
 */
interface Reset extends Basic
{
    /**
     * @param \Housekeeper\Contracts\Repository $repository
     * @param \Housekeeper\Contracts\Action     $action
     */
    public function __construct(Repository $repository, Action $action);
}
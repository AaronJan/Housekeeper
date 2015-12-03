<?php

namespace Housekeeper\Contracts\Flow;

use Housekeeper\Contracts\Action;

/**
 * Interface Basic
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Flow
 */
interface Basic
{
    /**
     * @return \Housekeeper\Contracts\Repository
     */
    public function getRepository();

    /**
     * @return \Housekeeper\Action
     */
    public function getAction();
}
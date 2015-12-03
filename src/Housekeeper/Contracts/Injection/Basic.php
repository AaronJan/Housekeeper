<?php

namespace Housekeeper\Contracts\Injection;

/**
 * Interface Basic
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Injection
 */
interface Basic
{
    /**
     * Lower is higher.
     *
     * @return integer
     */
    public function priority();
}

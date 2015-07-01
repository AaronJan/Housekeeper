<?php namespace Housekeeper\Contracts\Injection;

/**
 * Interface InjectionInterface
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Injection
 */
interface InjectionInterface
{

    /**
     * Lower is higher.
     *
     * @return integer
     */
    public function priority();

}

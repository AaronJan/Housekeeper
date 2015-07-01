<?php namespace Housekeeper\Contracts\Injection;

use Housekeeper\Flow\After;

/**
 * Interface AfterInjectionInterface
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Injection
 */
interface AfterInjectionInterface
{

    /**
     * @param After $flow
     * @return void
     */
    public function handle(After $flow);

}

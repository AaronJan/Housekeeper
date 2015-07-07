<?php

namespace Housekeeper\Contracts\Injection;

use Housekeeper\Flows\After;

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

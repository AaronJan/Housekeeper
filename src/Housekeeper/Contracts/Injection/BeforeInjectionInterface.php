<?php

namespace Housekeeper\Contracts\Injection;

use Housekeeper\Flows\Before;

/**
 * Interface BeforeInjectionInterface
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Injection
 */
interface BeforeInjectionInterface
{

    /**
     * @param Before $flow
     * @return void
     */
    public function handle(Before $flow);

}

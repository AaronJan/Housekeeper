<?php

namespace Housekeeper\Contracts\Injection;

use Housekeeper\Contracts\Flow\After as AfterFlow;

/**
 * Interface After
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Injection
 */
interface After extends Basic
{
    /**
     * @param \Housekeeper\Contracts\Flow\After $afterFlow
     * @return void
     */
    public function handle(AfterFlow $afterFlow);
}

<?php

namespace Housekeeper\Contracts\Injection;

use Housekeeper\Contracts\Flow\Before as BeforeFlow;

/**
 * Interface Before
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Injection
 */
interface Before extends Basic
{
    /**
     * @param \Housekeeper\Contracts\Flow\Before $beforeFlow
     * @return void
     */
    public function handle(BeforeFlow $beforeFlow);
}

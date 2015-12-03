<?php

namespace Housekeeper\Contracts\Injection;

use Housekeeper\Contracts\Flow\Reset as ResetFlow;

/**
 * Interface Reset
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts
 */
interface Reset extends Basic
{
    /**
     * @param \Housekeeper\Contracts\Flow\Reset $resetFlow
     * @return void
     */
    public function handle(ResetFlow $resetFlow);
}

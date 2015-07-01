<?php namespace Housekeeper\Contracts\Injection;

use Housekeeper\Flow\Reset;

/**
 * Interface ResetEventHandlerInterface
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts
 */
interface ResetInjectionInterface
{

    /**
     * @param Reset $flow
     * @return void
     */
    public function handle(Reset $flow);

}

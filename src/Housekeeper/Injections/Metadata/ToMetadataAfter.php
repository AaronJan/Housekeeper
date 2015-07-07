<?php

namespace Housekeeper\Injections\Metadata;

use Housekeeper\Contracts\Injection\AfterInjectionInterface;
use Housekeeper\Contracts\Injection\InjectionInterface;
use Housekeeper\Flows\After;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class ToMetadataAfter
 *
 * @priority 30
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Cacheable
 */
class ToMetadataAfter implements InjectionInterface,
                                 AfterInjectionInterface
{

    /**
     * @return int
     */
    public function priority()
    {
        return 30;
    }

    /**
     * @param After $flow
     */
    public function handle(After $flow)
    {
        $returnResult = $flow->getReturn();

        if ($returnResult instanceof Arrayable) {
            $flow->setReturn($returnResult->toArray());
        }
    }

}

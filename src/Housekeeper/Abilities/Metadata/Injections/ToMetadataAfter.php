<?php

namespace Housekeeper\Abilities\Metadata\Injections;

use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\After as AfterInjectionContract;
use Housekeeper\Contracts\Flow\After as AfterFlowContract;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class ToMetadataAfter
 *
 * @priority 30
 *
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Cacheable
 */
class ToMetadataAfter implements BasicInjectionContract,
                                 AfterInjectionContract
{
    /**
     * @return int
     */
    public function priority()
    {
        return 30;
    }

    /**
     * @param \Housekeeper\Contracts\Flow\After $afterFlow
     */
    public function handle(AfterFlowContract $afterFlow)
    {
        $returnResult = $afterFlow->getReturnValue();

        if ($returnResult instanceof Arrayable) {
            $afterFlow->setReturnValue($returnResult->toArray());
        }
    }
}

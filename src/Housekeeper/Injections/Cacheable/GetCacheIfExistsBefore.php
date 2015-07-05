<?php

namespace Housekeeper\Injections\Cacheable;

use Housekeeper\Action;
use Housekeeper\Contracts\Injection\InjectionInterface;
use Housekeeper\Contracts\Injection\BeforeInjectionInterface;
use Housekeeper\Flow\Before;

/**
 * Class GetCacheIfExistsBefore
 *
 * @priority 50
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Cacheable
 */
class GetCacheIfExistsBefore extends AbstractCacheManager implements InjectionInterface,
                                                                     BeforeInjectionInterface
{

    /**
     * @return int
     */
    public function priority()
    {
        return 50;
    }

    /**
     * @param Before $flow
     */
    public function handle(Before $flow)
    {
        $action = $flow->getAction();

        /**
         * Only get cache when Action is "Read".
         */
        if ($action->isType(Action::READ)) {
            $cacheKey = $this->getCacheKey($flow);

            $cachedValue = $this->getCache($cacheKey);

            if ( ! is_null($cachedValue)) {
                $flow->setReturn($cachedValue);
            }
        }
    }

}
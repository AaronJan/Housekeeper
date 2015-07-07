<?php

namespace Housekeeper\Injections\Cacheable;

use Housekeeper\Action;
use Housekeeper\Contracts\Injection\InjectionInterface;
use Housekeeper\Contracts\Injection\AfterInjectionInterface;
use Housekeeper\Flows\After;

/**
 * Class CacheResultAfter
 *
 * @priority 50
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Cacheable
 */
class CacheResultAfter extends AbstractCacheManager implements InjectionInterface,
                                                               AfterInjectionInterface
{

    /**
     * @return int
     */
    public function priority()
    {
        return 50;
    }

    /**
     * @param After $flow
     */
    public function handle(After $flow)
    {
        /**
         * Cache result only when action is "Read" type,
         * clear cache when action is "Create" or "Update".
         */
        $action = $flow->getAction();

        switch ($action->getType()) {
            case Action::CREATE:
                $this->forgetAll($flow);

                break;
            case Action::UPDATE:
                $this->forgetAll($flow);

                break;
            case Action::READ:
                $this->rememberResult($flow);

                break;
            default:
                break;
        }
    }

    /**
     * @param After $flow
     */
    protected function forgetAll(After $flow)
    {
        $cacheGroup = $this->getCacheGroup($flow->getRepository());

        $this->deleteCacheGroup($cacheGroup);
    }

    /**
     * @param After $flow
     */
    protected function rememberResult(After $flow)
    {
        $cacheGroup = $this->getCacheGroup($flow->getRepository());
        $cacheId    = $this->getCacheId($flow->getRepository(), $flow->getAction());

        $cacheKey = $cacheGroup . $cacheId;

        $this->setCache($cacheKey, $flow->getReturn());
    }

}
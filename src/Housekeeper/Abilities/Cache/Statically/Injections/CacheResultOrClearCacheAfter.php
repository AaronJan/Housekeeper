<?php

namespace Housekeeper\Abilities\Cache\Statically\Injections;

use Housekeeper\Action;
use Housekeeper\Contracts\Flow\After as AfterFlowContract;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\After as AfterInjectionContract;
use Housekeeper\Contracts\Repository;
use Housekeeper\Abilities\Cache\Statically;

/**
 * Class CacheResultOrClearCacheAfter
 *
 * @priority 50
 *
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Cacheable
 */
class CacheResultOrClearCacheAfter extends AbstractBase implements BasicInjectionContract,
                                                                   AfterInjectionContract
{
    /**
     * @return int
     */
    public function priority()
    {
        return 50;
    }

    /**
     * @param \Housekeeper\Contracts\Flow\After $afterFlow
     */
    public function handle(AfterFlowContract $afterFlow)
    {
        /**
         * Skip cache logic if cache has been disabled in the repository.
         *
         * @var $repository Repository|Statically
         */
        $repository   = $afterFlow->getRepository();
        $cacheEnabled = $repository->isCacheEnabled();

        /**
         * Cache result only when action is "Read" type,
         * clear cache when action is "Create" or "Update".
         */
        switch ($afterFlow->getAction()->getType()) {
            case Action::CREATE:
                $this->clearCache();

                break;
            case Action::UPDATE:
                $this->clearCache();

                break;
            case Action::READ:
                $cacheEnabled && $this->remember($afterFlow);

                break;
            case Action::DELETE:
                $this->clearCache();

                break;
            case Action::CREATE_OR_UPDATE:
                $this->clearCache();

                break;
            default:
                break;
        }
    }

    /**
     * @param \Housekeeper\Contracts\Flow\After $afterFlow
     */
    protected function remember(AfterFlowContract $afterFlow)
    {
        $this->setCacheForAction(
            $afterFlow->getAction(),
            $afterFlow->getReturnValue()
        );
    }

}
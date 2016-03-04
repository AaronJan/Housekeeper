<?php

namespace Housekeeper\Abilities\Cache\Individually\Injections;

use Housekeeper\Action;
use Housekeeper\Contracts\Flow\After as AfterFlowContract;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\After as AfterInjectionContract;
use Housekeeper\Contracts\Repository;
use Housekeeper\Abilities\Cache\Individually;

/**
 * Class CacheResultOrDeleteCacheAfter
 *
 * @priority 50
 *
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Cacheable
 */
class CacheResultOrDeleteCacheAfter extends AbstractBase implements BasicInjectionContract,
                                                                    AfterInjectionContract
{
    const PRIORITY = 50;


    /**
     * @return int
     */
    public function priority()
    {
        return static::PRIORITY;
    }

    /**
     * @param \Housekeeper\Contracts\Flow\After $afterFlow
     */
    public function handle(AfterFlowContract $afterFlow)
    {
        /**
         * Skip cache logic if cache has been disabled in the repository.
         *
         * @var $repository Repository|Individually
         */
        $repository   = $afterFlow->getRepository();
        $cacheEnabled = $repository->isCacheEnabled();

        /**
         * Cache result or delete cache.
         */
        switch ($afterFlow->getAction()->getMethodName()) {
            case static::UPDATE_BY_KEY_METHOD:
                $this->deleteCache(
                    $this->firstInArray($afterFlow->getAction()->getArguments())
                );

                break;
            case static::DELETE_BY_KEY_METHOD:
                $this->deleteCache(
                    $this->firstInArray($afterFlow->getAction()->getArguments())
                );

                break;
            case static::FIND_BY_KEY_METHOD:
                $cacheEnabled && $this->cacheResultIfCould($afterFlow);

                break;
            default:
                break;
        }
    }

    /**
     * @param \Housekeeper\Contracts\Flow\After $afterFlow
     */
    protected function cacheResultIfCould(AfterFlowContract $afterFlow)
    {
        $firstArgument = $this->firstInArray($afterFlow->getAction()->getArguments());

        // Skip when first argument is an array
        if ( ! is_array($firstArgument)) {
            $this->setCache($firstArgument, $afterFlow->getReturnValue());
        }
    }

}
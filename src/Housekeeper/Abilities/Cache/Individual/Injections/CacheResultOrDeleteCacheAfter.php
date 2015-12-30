<?php

namespace Housekeeper\Abilities\Cache\Individual\Injections;

use Housekeeper\Action;
use Housekeeper\Contracts\Flow\After as AfterFlowContract;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\After as AfterInjectionContract;
use Housekeeper\Contracts\Repository;
use Housekeeper\Abilities\Cache\Foundation\Base;

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
         * @var $repository Repository|Base
         */
        $repository = $afterFlow->getRepository();
        if ($repository->cacheEnabled() === false) {
            return;
        }

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
                $this->cacheResultIfCould($afterFlow);

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
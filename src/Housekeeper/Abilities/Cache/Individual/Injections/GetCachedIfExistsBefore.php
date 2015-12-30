<?php

namespace Housekeeper\Abilities\Cache\Individual\Injections;

use Housekeeper\Action;
use Housekeeper\Contracts\Flow\Before as BeforeFlowContract;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectionContract;
use Housekeeper\Contracts\Repository;
use Housekeeper\Abilities\Cache\Foundation\Base;

/**
 * Class GetCacheIfExistsBefore
 *
 * @priority 50
 *
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Cacheable
 */
class GetCachedIfExistsBefore extends AbstractBase implements BasicInjectionContract,
                                                              BeforeInjectionContract
{
    /**
     * @return int
     */
    public function priority()
    {
        return 50;
    }

    /**
     * @param \Housekeeper\Contracts\Flow\Before $beforeFlow
     */
    public function handle(BeforeFlowContract $beforeFlow)
    {
        /**
         * Skip cache logic if cache has been disabled in the repository.
         *
         * @var $repository Repository|Base
         */
        $repository = $beforeFlow->getRepository();
        if ($repository->cacheEnabled() === false) {
            return;
        }

        /**
         * Only get cache when using "find" method.
         */
        if ($beforeFlow->getAction()->getMethodName() == static::FIND_BY_KEY_METHOD) {
            $this->setReturnValueIfCould($beforeFlow);
        }
    }

    /**
     * @param \Housekeeper\Contracts\Flow\Before $beforeFlow
     */
    protected function setReturnValueIfCould(BeforeFlowContract $beforeFlow)
    {
        $firstArgument = $this->firstInArray($beforeFlow->getAction()->getArguments());

        // Skip when first argument is an array
        if ( ! is_array($firstArgument)) {
            $cached = $this->getCache($firstArgument);

            if ( ! is_null($cached)) {
                $beforeFlow->setReturnValue($cached);
            }
        }
    }

}
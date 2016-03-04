<?php

namespace Housekeeper\Abilities\Cache\Individually\Injections;

use Housekeeper\Action;
use Housekeeper\Contracts\Flow\Before as BeforeFlowContract;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectionContract;
use Housekeeper\Contracts\Repository;
use Housekeeper\Abilities\Cache\Individually;

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
    const PRIORITY = 50;


    /**
     * @return int
     */
    public function priority()
    {
        return static::PRIORITY;
    }

    /**
     * @param \Housekeeper\Contracts\Flow\Before $beforeFlow
     */
    public function handle(BeforeFlowContract $beforeFlow)
    {
        /**
         * Skip cache logic if cache has been disabled in the repository.
         *
         * @var $repository Repository|Individually
         */
        $repository = $beforeFlow->getRepository();
        if ($repository->isCacheEnabled() === false) {
            return;
        }

        /**
         * Only get cache when using "find" method.
         */
        if ($this->shouldUseCache($beforeFlow)) {
            $this->setReturnValueIfCould($beforeFlow);
        }
    }

    /**
     * @param \Housekeeper\Contracts\Flow\Before $beforeFlow
     * @return bool
     */
    protected function shouldUseCache(BeforeFlowContract $beforeFlow)
    {
        return (
            $beforeFlow->getAction()->getMethodName() == static::FIND_BY_KEY_METHOD &&
            $beforeFlow->getRepository()->getCurrentPlan()->isEmpty()
        );
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
<?php

namespace Housekeeper\Abilities\Cache\Statically\Injections;

use Housekeeper\Action;
use Housekeeper\Contracts\Flow\Before as BeforeFlowContract;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectionContract;
use Housekeeper\Contracts\Repository;
use Housekeeper\Abilities\Cache\Statically;

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
         * @var $repository Repository|Statically
         */
        $repository = $beforeFlow->getRepository();
        if ($repository->isCacheEnabled() === false) {
            return;
        }

        /**
         * Only get cache when Action is typed as "READ".
         */
        if ($beforeFlow->getAction()->isType(Action::READ)) {
            $cached = $this->getCacheForAction($beforeFlow->getAction());

            if ( ! is_null($cached)) {
                $beforeFlow->setReturnValue($cached);
            }
        }
    }

}
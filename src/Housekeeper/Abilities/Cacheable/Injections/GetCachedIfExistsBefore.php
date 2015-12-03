<?php

namespace Housekeeper\Abilities\Cacheable\Injections;

use Housekeeper\Action;
use Housekeeper\Contracts\Flow\Before as BeforeFlowContract;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectionContract;
use Housekeeper\Contracts\Repository;
use Housekeeper\Abilities\Cacheable\Cacheable;

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
         * @var $repository Repository|Cacheable
         */
        $repository = $beforeFlow->getRepository();
        if ($repository->cacheEnabled() === false) {
            return;
        }

        $this->setFlow($beforeFlow);

        /**
         * Only get cache when Action is typed as "READ".
         */
        if ($beforeFlow->getAction()->isType(Action::READ)) {
            $cached = $this->getCache($this->cacheIdentity());

            if ( ! is_null($cached)) {
                $beforeFlow->setReturnValue($cached);
            }
        }
    }

}
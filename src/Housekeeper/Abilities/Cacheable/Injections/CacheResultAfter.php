<?php

namespace Housekeeper\Abilities\Cacheable\Injections;

use Housekeeper\Action;
use Housekeeper\Contracts\Flow\After as AfterFlowContract;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\After as AfterInjectionContract;
use Housekeeper\Contracts\Repository;
use Housekeeper\Abilities\Cacheable\Cacheable;

/**
 * Class CacheResultAfter
 *
 * @priority 50
 *
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Cacheable
 */
class CacheResultAfter extends AbstractBase implements BasicInjectionContract,
                                                       AfterInjectionContract
{
    /**
     * @var \Housekeeper\Contracts\Flow\Basic|\Housekeeper\Flows\After
     */
    protected $flow;


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
         * @var $repository Repository|Cacheable
         */
        $repository = $afterFlow->getRepository();
        if ($repository->cacheEnabled() === false) {
            return;
        }

        $this->setFlow($afterFlow);

        /**
         * Cache result only when action is "Read" type,
         * clear cache when action is "Create" or "Update".
         */
        switch ($afterFlow->getAction()->getType()) {
            case Action::CREATE:
                $this->forgetAll();

                break;
            case Action::UPDATE:
                $this->forgetAll();

                break;
            case Action::READ:
                $this->remember();

                break;
            case Action::DELETE:
                $this->forgetAll();

                break;
            case Action::CREATE_OR_UPDATE:
                $this->forgetAll();

                break;
            default:
                break;
        }
    }

    /**
     *
     */
    protected function forgetAll()
    {
        $this->deleteCacheKey($this->cacheKey());
    }

    /**
     *
     */
    protected function remember()
    {
        $this->setCache($this->cacheIdentity(), $this->flow->getReturnValue());
    }

}
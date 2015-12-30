<?php

namespace Housekeeper\Abilities\Cache\Unforgettable\Injections;

use Housekeeper\Contracts\Action as ActionContract;
use Illuminate\Contracts\Redis\Database as RedisContract;
use Housekeeper\Abilities\Cache\Unforgettable\CacheAdapter;

/**
 * Class AbstractBase
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Abilities\Cache\Unforgettable\Injections
 */
abstract class AbstractBase
{
    /**
     * @var \Housekeeper\Abilities\Cache\Contracts\CacheAdapter
     */
    protected $cacheAdapter;


    /**
     * AbstractBase constructor.
     *
     * @param \Housekeeper\Abilities\Cache\Unforgettable\CacheAdapter $hashCacheAdapter
     */
    public function __construct(CacheAdapter $hashCacheAdapter)
    {
        $this->cacheAdapter = $hashCacheAdapter;
    }

    /**
     * @param \Housekeeper\Contracts\Action $action
     * @return mixed|null
     */
    protected function getCacheForAction(ActionContract $action)
    {
        return $this->cacheAdapter->getCacheForAction($action);
    }

    /**
     * @param \Housekeeper\Contracts\Action $action
     * @param                               $value
     */
    protected function setCacheForAction(ActionContract $action, $value)
    {
        $this->cacheAdapter->setCacheForAction($action, $value);
    }

    /**
     *
     */
    protected function clearCache()
    {
        $this->cacheAdapter->flush();
    }
}
<?php

namespace Housekeeper\Abilities\Cache;

use Housekeeper\Abilities\Cache\Unforgettable\CacheAdapter;
use Housekeeper\Abilities\Cache\Unforgettable\Injections\CacheResultOrClearCacheAfter;
use Housekeeper\Abilities\Cache\Unforgettable\Injections\GetCachedIfExistsBefore;


/**
 * Class Unforgettable
 *
 * @property \Illuminate\Contracts\Foundation\Application $app
 *
 * @method void inject(\Housekeeper\Contracts\Injection\Basic $injection, $sortAllInejctions = false)
 *
 * @package Housekeeper\Abilities\Cache
 */
trait Unforgettable
{
    use Foundation\Base;


    /**
     * Trait constructor, Inject all injections.
     */
    public function bootCacheUnforgettable()
    {
        $redis   = $this->getRedis();
        $configs = $this->getCacheConfigs([
            'prefix' => 'housekeeper_',
        ]);

        /**
         * @var $this \Housekeeper\Contracts\Repository|$this
         */
        $this->cacheAdapter = new CacheAdapter($this, $redis, $configs);

        $this->inject(new GetCachedIfExistsBefore($this->cacheAdapter), false);
        $this->inject(new CacheResultOrClearCacheAfter($this->cacheAdapter));
    }

}
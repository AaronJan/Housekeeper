<?php

namespace Housekeeper\Abilities\Cache;

use Housekeeper\Abilities\Cache\Statically\CacheAdapter;
use Housekeeper\Abilities\Cache\Statically\Injections\CacheResultOrClearCacheAfter;
use Housekeeper\Abilities\Cache\Statically\Injections\GetCachedIfExistsBefore;


/**
 * Class Statically
 *
 * @property \Illuminate\Contracts\Foundation\Application $app
 *
 * @method void inject(\Housekeeper\Contracts\Injection\Basic $injection, $sortAllInejctions = false)
 *
 * @package Housekeeper\Abilities\Cache
 */
trait Statically
{
    use Foundation\Base;


    /**
     * Trait constructor, Inject all injections.
     */
    public function bootCacheStatically()
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
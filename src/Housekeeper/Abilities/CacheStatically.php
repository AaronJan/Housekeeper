<?php

namespace Housekeeper\Abilities;

use Housekeeper\Abilities\Cache\Statically\CacheAdapter;
use Housekeeper\Abilities\Cache\Statically\Injections\CacheResultOrClearCacheAfter;
use Housekeeper\Abilities\Cache\Statically\Injections\GetCachedIfExistsBefore;

/**
 * Class CacheStatically
 *
 * @property \Illuminate\Contracts\Foundation\Application $app
 *
 * @method void injectIntoBefore(\Housekeeper\Contracts\Injection\Before $injection, $sort = true)
 * @method void injectIntoAfter(\Housekeeper\Contracts\Injection\After $injection, $sort = true)
 *
 * @package Housekeeper\Abilities\Cache
 */
trait CacheStatically
{
    use Cache\Foundation\Base;

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
         * @var $this CacheStatically|\Housekeeper\Contracts\Repository
         */
        $this->cacheAdapter = new CacheAdapter($this, $redis, $configs);

        $this->injectIntoBefore(new GetCachedIfExistsBefore($this->cacheAdapter), false);
        $this->injectIntoAfter(new CacheResultOrClearCacheAfter($this->cacheAdapter));
    }

}
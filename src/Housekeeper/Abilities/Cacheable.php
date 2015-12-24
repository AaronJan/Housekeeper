<?php

namespace Housekeeper\Abilities;

use Housekeeper\Abilities\Cacheable\Injections\CacheResultAfter;
use Housekeeper\Abilities\Cacheable\Injections\GetCachedIfExistsBefore;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Redis\Database as RedisContract;

/**
 * Class Cacheable
 *
 * @property \Illuminate\Contracts\Foundation\Application $app
 *
 * @method void inject(\Housekeeper\Contracts\Injection\Basic $injection, $sortAllInejctions = false)
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Cacheable
{
    /**
     * @var bool
     */
    protected $cacheEnabled = true;

    
    /**
     * Trait constructor, Inject all injections.
     */
    public function setupCache()
    {
        $redis   = $this->getRedis();
        $configs = $this->getConfigs();

        $this->inject(new GetCachedIfExistsBefore($redis, $configs), false);
        $this->inject(new CacheResultAfter($redis, $configs));
    }

    /**
     *
     */
    public function disableCache()
    {
        $this->cacheEnabled = false;

        return $this;
    }

    /**
     *
     */
    public function enableCache()
    {
        $this->cacheEnabled = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function cacheEnabled()
    {
        return $this->cacheEnabled;
    }
    
    /**
     * @return \Illuminate\Contracts\Redis\Database
     */
    private function getRedis()
    {
        try {
            return $this->app->make('redis');
        } catch (\Exception $e) {
            throw new \RuntimeException('Cacheable trait of Housekeeper requires Redis support.');
        }
    }

    /**
     * @return array
     */
    private function getConfigs()
    {
        $configRepository = $this->app->make('config');

        $configs = [
            'prefix' => $configRepository->get('housekeeper.abilities.cacheable.cache.prefix', 'housekeeper_'),
        ];

        return $configs;
    }
}
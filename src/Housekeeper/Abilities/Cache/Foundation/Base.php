<?php

namespace Housekeeper\Abilities\Cache\Foundation;

/**
 * Class Base
 *
 * @property \Illuminate\Contracts\Foundation\Application $app
 *
 * @method \Illuminate\Contracts\Foundation\Application getApp()
 * @method mixed getConfig($key, $default = null)
 *
 * @package Housekeeper\Abilities\Cache\Foundation
 */
trait Base
{
    /**
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * @var \Housekeeper\Abilities\Cache\Contracts\CacheAdapter
     */
    protected $cacheAdapter;

    /**
     * @var \Illuminate\Contracts\Redis\Database
     */
    protected $cacheRedis;
    

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
    public function isCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    /**
     *
     */
    public function clearCache()
    {
        $this->cacheAdapter->flush();

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Redis\Database|\Illuminate\Redis\Database
     */
    protected function getRedis()
    {
        if (! $this->cacheRedis) {
            try {
                $this->cacheRedis = $this->getApp()->make('redis');
            } catch (\Exception $e) {
                throw new \RuntimeException('Cacheable trait of Housekeeper requires Redis support.');
            }
        }

        return $this->cacheRedis;
    }

    /**
     * @param array $default
     * @return mixed
     */
    protected function getCacheConfigs(array $default = [])
    {
        return $this->getConfig('housekeeper.abilities.cache', $default);
    }

}
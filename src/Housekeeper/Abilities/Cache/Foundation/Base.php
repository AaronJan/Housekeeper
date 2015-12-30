<?php

namespace Housekeeper\Abilities\Cache\Foundation;

/**
 * Class Base
 *
 * @property \Illuminate\Contracts\Foundation\Application $app
 *
 * @method \Illuminate\Contracts\Foundation\Application getApp()
 *
 * @package Housekeeper\Abilities\Cache\Foundation
 */
trait Base
{
    /**
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * @var \Housekeeper\Abilities\Cache\Contracts\CacheAdapter
     */
    protected $cacheAdapter;


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
     *
     */
    public function clearCache()
    {
        $this->cacheAdapter->flush();

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Redis\Database
     */
    private function getRedis()
    {
        try {
            return $this->getApp()->make('redis');
        } catch (\Exception $e) {
            throw new \RuntimeException('Cacheable trait of Housekeeper requires Redis support.');
        }
    }

    /**
     * @param array $default
     * @return mixed
     */
    private function getCacheConfigs(array $default = [])
    {
        $config = $this->getApp()->make('config');

        $cacheConfigs = $config->get(
            'housekeeper.abilities.cache',
            $default
        );

        return $cacheConfigs;
    }

}
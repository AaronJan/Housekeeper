<?php

namespace Housekeeper\Abilities\Cache\Individually;

use Housekeeper\Contracts\Repository as RepositoryContract;
use Housekeeper\Contracts\Action as ActionContract;
use Illuminate\Contracts\Redis\Database as RedisContract;
use Housekeeper\Support\SmartHasher;
use Housekeeper\Abilities\Cache\Contracts\CacheAdapter as CacheAdapterContract;

/**
 * Class CacheAdapter
 *
 * @package Housekeeper\Abilities\Cache\Individually
 */
class CacheAdapter implements CacheAdapterContract
{
    /**
     * @var \Illuminate\Contracts\Redis\Database|\Illuminate\Redis\Database
     */
    protected $redis;

    /**
     * @var \Housekeeper\Contracts\Repository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $cacheKey;


    /**
     * CacheManager constructor.
     *
     * @param \Housekeeper\Contracts\Repository    $repository
     * @param \Illuminate\Contracts\Redis\Database $redis
     * @param                                      $configs
     */
    public function __construct(RepositoryContract $repository,
                                RedisContract $redis,
                                array $configs)
    {
        $this->repository = $repository;
        $this->redis      = $redis;

        $this->prefix = $configs['prefix'];
    }

    /**
     * @return \Illuminate\Contracts\Redis\Database|\Illuminate\Redis\Database
     */
    protected function getRedis()
    {
        return $this->redis;
    }

    /**
     * @return \Housekeeper\Contracts\Repository
     */
    protected function getRepository()
    {
        return $this->repository;
    }

    /**
     *
     */
    public function flush()
    {
        $this->getRedis()->del($this->cacheKey());
    }
    
    /**
     * @param $primaryKey
     * @param $value
     */
    public function setCache($primaryKey, $value)
    {
        $this->getRedis()->hset(
            $this->cacheKey(),
            $primaryKey,
            serialize($value)
        );
    }

    /**
     * @param $primaryKey
     * @return mixed|null
     */
    public function getCache($primaryKey)
    {
        $cachedValue = $this->getRedis()->hget(
            $this->cacheKey(),
            $primaryKey
        );

        return is_null($cachedValue) ? null : unserialize($cachedValue);
    }

    /**
     * @param $primaryKey
     * @return bool
     */
    public function deleteCache($primaryKey)
    {
        return (bool) $this->getRedis()->hdel(
            $this->cacheKey(),
            $primaryKey
        );
    }

    /**
     * @return string
     */
    protected function cacheKey()
    {
        if (! $this->cacheKey) {
            $this->cacheKey = $this->prefix .
                str_replace(
                    '\\', '#',
                    get_class($this->getRepository())
                );
        }

        return $this->cacheKey;
    }

}
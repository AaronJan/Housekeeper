<?php

namespace Housekeeper\Abilities\Cache\Unforgettable;

use Housekeeper\Contracts\Repository as RepositoryContract;
use Housekeeper\Contracts\Action as ActionContract;
use Illuminate\Contracts\Redis\Database as RedisContract;
use Housekeeper\Support\SmartHasher;
use Housekeeper\Abilities\Cache\Contracts\CacheAdapter as CacheAdapterContract;

/**
 * Class CacheAdapter
 *
 * @package Housekeeper\Abilities\Cache\Unforgettable
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
     * @param \Housekeeper\Contracts\Action $action
     * @param                               $value
     */
    public function setCacheForAction(ActionContract $action, $value)
    {
        $this->getRedis()->hset(
            $this->cacheKey(),
            $this->getCacheFieldForAction($action),
            serialize($value)
        );
    }

    /**
     * @param \Housekeeper\Contracts\Action $action
     * @return mixed|null
     */
    public function getCacheForAction(ActionContract $action)
    {
        $cachedValue = $this->getRedis()->hget(
            $this->cacheKey(),
            $this->getCacheFieldForAction($action)
        );

        return is_null($cachedValue) ? null : unserialize($cachedValue);
    }

    /**
     *
     */
    public function flush()
    {
        $this->getRedis()->del($this->cacheKey());
    }

    /**
     * @param \Housekeeper\Contracts\Action $action
     * @return string
     */
    protected function getCacheFieldForAction(ActionContract $action)
    {
        $id = md5(
            SmartHasher::hash($this->getRepository()->getCurrentPlan()) .
            $action->getMethodName() .
            SmartHasher::hash($action->getArguments())
        );

        return $id;
    }

    /**
     * @return string
     */
    protected function cacheKey()
    {
        if ( ! $this->cacheKey) {
            $this->cacheKey = $this->prefix .
                str_replace(
                    '\\', '#',
                    get_class($this->getRepository())
                );
        }

        return $this->cacheKey;
    }
}
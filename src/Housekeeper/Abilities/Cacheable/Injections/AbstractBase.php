<?php

namespace Housekeeper\Abilities\Cacheable\Injections;

use Housekeeper\Support\SmartHasher;
use Housekeeper\Contracts\Flow\Basic as FlowContract;
use Illuminate\Contracts\Redis\Database as RedisContract;

/**
 * Class AbstractBase
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Injections\Cacheable
 */
abstract class AbstractBase
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var \Illuminate\Redis\Database
     */
    protected $redis;

    /**
     * @var \Housekeeper\Contracts\Flow\Basic
     */
    protected $flow;


    /**
     * AbstractCacheManager constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(RedisContract $redis, array $configs)
    {
        $this->redis = $redis;

        $this->prefix = $configs['prefix'];
    }

    /**
     * @param \Housekeeper\Contracts\Flow\Basic $flow
     */
    protected function setFlow(FlowContract $flow)
    {
        $this->flow = $flow;
    }

    /**
     * @return string
     */
    protected function cacheKey()
    {
        $key = str_replace(
            '\\', '#',
            get_class($this->flow->getRepository())
        );

        return ($this->prefix . $key);
    }

    /**
     * @return string
     */
    protected function cacheField()
    {
        $repository = $this->flow->getRepository();
        $action     = $this->flow->getAction();

        $id = md5(
            SmartHasher::hash($repository->getCurrentPlan()) .
            $action->getMethodName() .
            SmartHasher::hash($action->getArguments())
        );

        return $id;
    }

    /**
     * @return array
     */
    protected function cacheIdentity()
    {
        return [$this->cacheKey(), $this->cacheField()];
    }

    /**
     * @param array $identity
     * @return mixed|null
     */
    protected function getCache(array $identity)
    {
        list($key, $field) = $identity;

        $cachedValue = $this->redis->hget($key, $field);

        return is_null($cachedValue) ? null : unserialize($cachedValue);
    }

    /**
     * @param array $identity
     * @param       $value
     */
    protected function setCache(array $identity, $value)
    {
        list($key, $field) = $identity;

        $this->redis->hset($key, $field, serialize($value));
    }

    /**
     * @param $key
     */
    protected function deleteCacheKey($key)
    {
        $this->redis->del($key);
    }
}
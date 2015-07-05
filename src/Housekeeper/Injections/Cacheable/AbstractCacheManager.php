<?php

namespace Housekeeper\Injections\Cacheable;

use Illuminate\Contracts\Cache\Repository;
use Housekeeper\Contracts\RepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Housekeeper\Action;
use Illuminate\Redis\Database;
use Housekeeper\Flow\After;
use Housekeeper\Contracts\Flow\Basic as FlowContract;

/**
 * Class AbstractCacheManager
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Injections\Cacheable
 */
abstract class AbstractCacheManager
{

    /**
     * @var Repository
     */
    protected $cacheRepository;

    /**
     * Cache live in second.
     *
     * @var int
     */
    protected $cacheLive = 300;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Database
     */
    protected $redis;


    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app   = $app;
        $this->redis = $this->app->make('redis');

        $this->loadConfig();
    }

    /**
     * Load configures from config file.
     */
    protected function loadConfig()
    {
        $config = $this->app->make('config');

        /**
         * How many entries per page.
         */
        $this->cacheLive = $config->get('housekeeper.repository.cache.live', 300);
    }

    /**
     * @param RepositoryInterface $repository
     * @return mixed
     */
    protected function getCacheGroup(RepositoryInterface $repository)
    {
        $group = str_replace('\\', '#', get_class($repository)) . ':';

        return $group;
    }

    /**
     * @param RepositoryInterface $repository
     * @param Action              $action
     * @return string
     */
    protected function getCacheId(RepositoryInterface $repository,
                                  Action $action)
    {
        $id = md5(
            serialize($repository->getConditions()) .
            $action->getMethodName() .
            json_encode($action->getArguments())
        );

        return $id;
    }

    /**
     * @param FlowContract $flow
     * @return string
     */
    protected function getCacheKey(FlowContract $flow)
    {
        $cacheGroup = $this->getCacheGroup($flow->getRepository());
        $cacheId    = $this->getCacheId($flow->getRepository(), $flow->getAction());

        $cacheKey = $cacheGroup . $cacheId;

        return $cacheKey;
    }

    /**
     * @param $key
     * @return null|string
     */
    protected function getCache($key)
    {
        $cachedValue = $this->redis->get($key);

        return is_null($cachedValue) ? null : unserialize($cachedValue);
    }

    /**
     * @param          $key
     * @param mixed    $value
     * @param null|int $cacheLive
     * @return mixed
     */
    protected function setCache($key, $value, $cacheLive = null)
    {
        $cacheLive = ! is_null($cacheLive) ? $cacheLive : $this->cacheLive;

        return $this->redis->set($key, serialize($value), 'ex', $cacheLive);
    }

    /**
     * @param $group
     */
    protected function deleteCacheGroup($group)
    {
        $keys = $this->redis->keys($group . '*');

        if ( ! empty($keys)) {
            $this->redis->del($keys);
        }
    }

}
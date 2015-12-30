<?php

namespace Housekeeper\Abilities\Cache\Individual\Injections;

use Housekeeper\Contracts\Action as ActionContract;
use Illuminate\Contracts\Redis\Database as RedisContract;
use Housekeeper\Abilities\Cache\Individual\CacheAdapter;

/**
 * Class AbstractBase
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Abilities\Cache\Unforgettable\Injections
 */
abstract class AbstractBase
{
    /**
     * @var string
     */
    const FIND_BY_KEY_METHOD = '_find';

    /**
     * @var string
     */
    const UPDATE_BY_KEY_METHOD = '_update';

    /**
     * @var string
     */
    const DELETE_BY_KEY_METHOD = '_delete';

    /**
     * @var \Housekeeper\Abilities\Cache\Contracts\CacheAdapter
     */
    protected $cacheAdapter;


    /**
     * AbstractBase constructor.
     *
     * @param \Housekeeper\Abilities\Cache\Unforgettable\CacheAdapter $hashCacheAdapter
     */
    public function __construct(CacheAdapter $hashCacheAdapter)
    {
        $this->cacheAdapter = $hashCacheAdapter;
    }
    
    /**
     * @param $array
     * @return null
     */
    protected function firstInArray($array)
    {
        foreach ($array as $entry) {
            return $entry;
        }

        return null;
    }

    /**
     *
     */
    protected function clearCache()
    {
        $this->cacheAdapter->flush();
    }
    
    /**
     * @param $primaryKey
     * @return mixed
     */
    protected function getCache($primaryKey)
    {
        return $this->cacheAdapter->getCache($primaryKey);
    }

    /**
     * @param $primaryKey
     * @param $value
     */
    protected function setCache($primaryKey, $value)
    {
        $this->cacheAdapter->setCache($primaryKey, $value);
    }

    /**
     * @param $primaryKey
     */
    protected function deleteCache($primaryKey)
    {
        $this->cacheAdapter->deleteCache($primaryKey);
    }
}
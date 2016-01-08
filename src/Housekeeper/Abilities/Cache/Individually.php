<?php

namespace Housekeeper\Abilities\Cache;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Housekeeper\Abilities\Cache\Individually\CacheAdapter;
use Housekeeper\Abilities\Cache\Individually\Injections\GetCachedIfExistsBefore;
use Housekeeper\Abilities\Cache\Individually\Injections\CacheResultOrDeleteCacheAfter;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Individually
 *
 * @property \Illuminate\Contracts\Foundation\Application           $app
 * @property integer                                                $perPage
 * @property \Housekeeper\Abilities\Cache\Individually\CacheAdapter $cacheAdapter
 *
 * @method string getKeyName()
 * @method \Illuminate\Database\Eloquent\Collection findMany($id, $columns = ['*'])
 * @method \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder getModel()
 * @method void inject(\Housekeeper\Contracts\Injection\Basic $injection, $sortAllInejctions = false)
 * @method mixed find($id, $columns = ['*'])
 *
 * @package Housekeeper\Abilities\Cache
 */
trait Individually
{
    use Foundation\Base;


    /**
     *
     */
    public function bootCacheIndividually()
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
        $this->inject(new CacheResultOrDeleteCacheAfter($this->cacheAdapter));
    }
    
    /**
     * @param $primaryKey
     * @return bool
     */
    public function deleteCache($primaryKey)
    {
        return $this->cacheAdapter->deleteCache($primaryKey);
    }
    
    /**
     * @param       $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    protected function _find($id, $columns = ['*'])
    {
        if (is_array($id)) {
            return $this->findMany($id, $columns);
        }

        return $this->getModel()->find($id, $columns);
    }

    /**
     * @param array $ids
     * @param array $columns
     * @return EloquentCollection
     */
    protected function _findMany($ids, $columns = ['*'])
    {
        $entries = [];
        foreach ($ids as $id) {
            $entries[] = $this->find($id);
        }

        return new EloquentCollection($entries);
    }
}
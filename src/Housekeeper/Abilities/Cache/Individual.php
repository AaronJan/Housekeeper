<?php

namespace Housekeeper\Abilities\Cache;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Housekeeper\Abilities\Cache\Individual\CacheAdapter;
use Housekeeper\Abilities\Cache\Individual\Injections\GetCachedIfExistsBefore;
use Housekeeper\Abilities\Cache\Individual\Injections\CacheResultOrDeleteCacheAfter;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Individual
 *
 * @property \Illuminate\Contracts\Foundation\Application $app
 * @property integer                                      $perPage
 *
 * @method \Illuminate\Database\Eloquent\Collection findMany($id, $columns = ['*'])
 * @method \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder getModel()
 * @method void inject(\Housekeeper\Contracts\Injection\Basic $injection, $sortAllInejctions = false)
 * @method mixed find($id, $columns = ['*'])
 *
 * @package Housekeeper\Abilities\Cache
 */
trait Individual
{
    use Foundation\Base;


    /**
     *
     */
    public function setupCacheIndividual()
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
     * @param array $columns
     * @return EloquentCollection
     */
    protected function _all($columns = ['*'])
    {
        $model          = $this->getModel();
        $primaryKeyName = $model->getKeyName();

        $primaryKeys = $model
            ->get([$primaryKeyName])
            ->pluck($primaryKeyName);

        $entries = [];
        foreach ($primaryKeys as $primaryKey) {
            $entries[] = $this->find($primaryKey);
        }

        return new EloquentCollection($entries);
    }

    /**
     * @param int|null $limit
     * @param array    $columns
     * @return LengthAwarePaginator
     */
    protected function _paginate($limit = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $model          = $this->getModel();
        $primaryKeyName = $model->getKeyName();

        $limit = $limit ?: $this->perPage;

        $liteEntries = $model->paginate($limit, [$primaryKeyName], $pageName, $page);

        $entries = [];
        foreach ($liteEntries->items() as $liteEntry) {
            $entries[] = $this->find($liteEntry->$primaryKeyName);
        }

        return new LengthAwarePaginator(
            $entries,
            $liteEntries->total(),
            $liteEntries->perPage(),
            $liteEntries->currentPage()
        );
    }

    /**
     * @param       $field
     * @param null  $value
     * @param array $columns
     * @return array|EloquentCollection|static[]
     */
    protected function _getByField($field, $value = null, $columns = ['*'])
    {
        $model          = $this->getModel();
        $primaryKeyName = $model->getKeyName();

        $primaryKeys = $model
            ->where($field, '=', $value)
            ->get([$primaryKeyName])
            ->pluck($primaryKeyName);

        $entries = [];
        foreach ($primaryKeys as $primaryKey) {
            $entries[] = $this->find($primaryKey);
        }

        return new EloquentCollection($entries);
    }

    /**
     * @param       $field
     * @param null  $value
     * @param array $columns
     * @return Model|static
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function _findByField($field, $value = null, $columns = ['*'])
    {
        $model          = $this->getModel();
        $primaryKeyName = $model->getKeyName();

        $primaryKey = $model
            ->where($field, '=', $value)
            ->firstOrFail([$primaryKeyName])
            ->$primaryKeyName;

        return $this->find($primaryKey);
    }

    /**
     * @param array $ids
     * @param array $columns
     * @return EloquentCollection
     */
    protected function _findMany($ids, $columns = ['*'])
    {
        $model          = $this->getModel();
        $primaryKeyName = $model->getKeyName();

        $entries = [];
        foreach ($ids as $id) {
            $entries[] = $this->find($id);
        }

        return new EloquentCollection($entries);
    }

}
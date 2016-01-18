<?php

namespace Housekeeper\Contracts;

/**
 * Interface Repository
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts
 */
interface Repository
{
    /**
     * @return \Housekeeper\Contracts\Plan
     */
    public function getCurrentPlan();

    /**
     * @param array $wheres
     * @return mixed
     */
    public function applyWheres(array $wheres);

    /**
     * @param        $column
     * @param string $direction
     * @return mixed
     */
    public function applyOrderBy($column, $direction = 'asc');

    /**
     * @return mixed
     */
    public function with();

    /**
     * @param      $id
     * @param null $column
     * @return mixed
     */
    public function exists($id, $column = null);

    /**
     * @param       $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * @param       $ids
     * @param array $columns
     * @return mixed
     */
    public function findMany($ids, $columns = ['*']);

    /**
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param       $id
     * @param array $attributes
     * @return mixed
     */
    public function update($id, array $attributes);

    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * @param null   $limit
     * @param array  $columns
     * @param string $pageName
     * @param null   $page
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * @param       $field
     * @param null  $value
     * @param array $columns
     * @return mixed
     */
    public function getByField($field, $value = null, $columns = ['*']);

    /**
     * @param       $field
     * @param null  $value
     * @param array $columns
     * @return mixed
     */
    public function findByField($field, $value = null, $columns = ['*']);
}

<?php

namespace Housekeeper\Contracts;

/**
 * Interface Plan
 *
 * @package Housekeeper\Contracts
 */
interface Plan
{
    /**
     * @return array
     */
    public function getConditions();

    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function getModel();

    /**
     * @param  string $column
     * @param string  $direction
     * @return $this
     */
    public function applyOrderBy($column, $direction = 'asc');

    /**
     * @param array $wheres
     * @return $this
     */
    public function applyWheres(array $wheres);

    /**
     * @param  mixed $relations
     * @return $this
     */
    public function with();

    /**
     * @return $this
     */
    public function startWithTrashed();

    /**
     * @return $this
     */
    public function startWithTrashedOnly();
}

<?php

namespace Housekeeper;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Plan
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper
 */
class Plan implements Contracts\Plan
{
    /**
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected $model;

    /**
     * @var array
     */
    protected $conditions = [];
    

    /**
     * Plan constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Every single action that would afftected returns, should add a condition.
     *
     * @param string $type
     * @param mixed  $condition
     */
    protected function addCondition($type, $condition)
    {
        $this->conditions[] = [
            $type => $condition,
        ];
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function getModel()
    {
        return $this->model;
    }
    
    /**
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function applyOrderBy($column, $direction = 'asc')
    {
        /**
         * Save to conditons.
         */
        $this->addCondition('order by', [$column, $direction]);

        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * @param array $wheres
     * @return $this
     */
    public function applyWheres(array $wheres)
    {
        /**
         * Save to conditons.
         */
        $this->addCondition('wheres', $wheres);

        foreach ($wheres as $key => $where) {
            $this->model = call_user_func_array([$this->model, 'where'], $where);
        }

        return $this;
    }

    /**
     * @param  mixed $relations
     * @return $this
     */
    public function with()
    {
        $arguments = func_get_args();

        /**
         * Save to conditons.
         */
        $this->addCondition('with', $arguments);

        call_user_func(array($this->model, 'with'), $arguments);

        return $this;
    }

    /**
     * Reset and includes soft deletes for following queries.
     *
     * @return $this
     */
    public function startWithTrashed()
    {
        /**
         * Save to conditons.
         */
        $this->addCondition('withTrashed', 'withTrashed');

        $this->model = $this->model->withTrashed();

        return $this;
    }

    /**
     * Reset and only includes soft deletes for following queries.
     *
     * @return $this
     */
    public function startWithTrashedOnly()
    {
        /**
         * Save to conditons.
         */
        $this->addCondition('onlyTrashed', 'onlyTrashed');

        $this->model = $this->model->onlyTrashed();

        return $this;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return [
            'conditions',
        ];
    }
}

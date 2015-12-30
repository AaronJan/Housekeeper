<?php

namespace Housekeeper\Abilities;

/**
 * Class Understanding
 *
 * This trait provide frequently-used Eloquent-Style query methods.
 *
 * @method void inject(\Housekeeper\Contracts\Injection\Basic $injection, $sortAllInejctions = false)
 * @method $this applyWheres(array $wheres)
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Understanding
{
    /**
     * @param        $column
     * @param null   $operator
     * @param null   $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->applyWheres([
            [$column, $operator, $value, $boolean],
        ]);

        return $this;
    }

    /**
     * @param      $column
     * @param null $operator
     * @param null $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        $this->applyWheres([
            [$column, $operator, $value, 'or'],
        ]);

        return $this;
    }

    /**
     * @param               $relation
     * @param string        $operator
     * @param int           $count
     * @param string        $boolean
     * @param \Closure|null $callback
     * @return $this
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', \Closure $callback = null)
    {
        $this->applyWheres([
            function ($query) use (&$relation, &$operator, &$count, &$boolean, &$callback) {
                /**
                 * @var $query \Illuminate\Database\Eloquent\Builder
                 */
                $query->has($relation, $operator, $count, $boolean, $callback);
            },
        ]);

        return $this;
    }
    
    /**
     * @param          $relation
     * @param \Closure $callback
     * @param string   $operator
     * @param int      $count
     * @return $this
     */
    public function whereHas($relation, \Closure $callback, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'and', $callback);
    }

    /**
     * @param               $relation
     * @param \Closure|null $callback
     * @return $this
     */
    public function whereDoesntHave($relation, \Closure $callback = null)
    {
        return $this->has($relation, '<', 1, 'and', $callback);
    }

    /**
     * @param                                         $relation
     * @param \Closure                                $callback
     * @param string                                  $operator
     * @param int                                     $count
     * @return $this
     */
    public function orWhereHas($relation, \Closure $callback, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or', $callback);
    }
}
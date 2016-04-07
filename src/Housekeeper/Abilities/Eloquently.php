<?php

namespace Housekeeper\Abilities;

use Illuminate\Database\Query\Builder;

/**
 * Class Eloquently
 *
 * This trait provide frequently-used Eloquent-Style query methods.
 *
 * @method $this whereAre(array $wheres)
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Eloquently
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
        $this->whereAre([
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
        $this->whereAre([
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
        $this->whereAre([
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
     * @return \Housekeeper\Abilities\Eloquently
     */
    public function whereDoesntHave($relation, \Closure $callback = null)
    {
        return $this->has($relation, '<', 1, 'and', $callback);
    }

    /**
     * @param          $relation
     * @param \Closure $callback
     * @param string   $operator
     * @param int      $count
     * @return \Housekeeper\Abilities\Eloquently
     */
    public function orWhereHas($relation, \Closure $callback, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or', $callback);
    }
    
    /**
     * @param        $column
     * @param        $values
     * @param string $boolean
     * @param bool   $not
     * @return \Housekeeper\Abilities\Eloquently
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        return $this->whereAre([
            function ($query) use ($column, $values, $boolean, $not) {
                /**
                 * @var $query Builder
                 */
                $query->whereIn($column, $values, $boolean, $not);
            },
        ]);
    }

    /**
     * @param        $column
     * @param        $values
     * @param string $boolean
     * @return \Housekeeper\Abilities\Eloquently
     */
    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * @param $column
     * @param $values
     * @return \Housekeeper\Abilities\Eloquently
     */
    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * @param        $column
     * @param string $boolean
     * @param bool   $not
     * @return \Housekeeper\Abilities\Eloquently
     */
    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        return $this->whereAre([
            compact('type', 'column', 'boolean'),
        ]);
    }

    /**
     * @param $column
     * @return \Housekeeper\Abilities\Eloquently
     */
    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'or');
    }

    /**
     * @param        $column
     * @param string $boolean
     * @return \Housekeeper\Abilities\Eloquently
     */
    public function whereNotNull($column, $boolean = 'and')
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * @param $column
     * @return \Housekeeper\Abilities\Eloquently
     */
    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'or');
    }
}
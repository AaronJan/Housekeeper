<?php

namespace Housekeeper\Abilities;

/**
 * Class Vintage
 *
 * This trait provide backward compatible APIs for Housekeeper `0.9.x`.
 *
 * @method $this whereAre(array $wheres)
 * @method $this applyOrderBy($column, $direction = 'asc')
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Vintage
{
    /**
     * @param array $wheres
     * @return $this
     */
    public function applyWhere(array $wheres)
    {
        return $this->whereAre($wheres);
    }
    
    /**
     * @param        $column
     * @param string $direction
     * @return mixed
     */
    public function applyOrder($column, $direction = 'asc')
    {
        return $this->applyOrderBy($column, $direction);
    }
}
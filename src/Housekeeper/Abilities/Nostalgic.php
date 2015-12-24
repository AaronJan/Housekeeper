<?php

namespace Housekeeper\Abilities;

/**
 * Class Nostalgic
 *
 * This trait provide backward compatible APIs for Housekeeper `0.9.x`.
 *
 * @method void inject(\Housekeeper\Contracts\Injection\Basic $injection, $sortAllInejctions = false)
 * @method $this applyWheres(array $wheres)
 * @method $this applyOrderBy($column, $direction = 'asc')
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Nostalgic
{
    /**
     * @param array $wheres
     * @return $this
     */
    public function applyWhere(array $wheres)
    {
        return $this->applyWheres($wheres);
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
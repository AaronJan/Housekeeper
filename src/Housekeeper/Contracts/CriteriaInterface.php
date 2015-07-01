<?php namespace Housekeeper\Contracts;

use Housekeeper\Contracts\RepositoryInterface;

/**
 * Interface CriteriaInterface
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts
 */
interface CriteriaInterface
{

    /**
     * Apply criteria to repository
     *
     * @param RepositoryInterface $repository
     * @return Model
     */
    public function apply(RepositoryInterface $repository);

}
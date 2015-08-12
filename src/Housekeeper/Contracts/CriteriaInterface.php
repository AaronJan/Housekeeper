<?php

namespace Housekeeper\Contracts;

use Housekeeper\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

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
     */
    public function apply(RepositoryInterface $repository);

}
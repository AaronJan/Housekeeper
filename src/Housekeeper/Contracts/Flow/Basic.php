<?php

namespace Housekeeper\Contracts\Flow;

use Housekeeper\Contracts\RepositoryInterface;
use Housekeeper\Action;

/**
 * Interface Basic
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Contracts\Flow
 */
interface Basic
{

    /**
     * @return RepositoryInterface
     */
    public function getRepository();

    /**
     * @return Action
     */
    public function getAction();

}
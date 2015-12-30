<?php

namespace Housekeeper\Abilities\Cache\Contracts;

/**
 * Interface CacheAdapter
 *
 * @package Housekeeper\Abilities\Cache\Contracts
 */
interface CacheAdapter
{
    /**
     * @return void
     */
    public function flush();
}
<?php

namespace Housekeeper\Abilities;

use Housekeeper\Abilities\Metadata\Injections\ToMetadataAfter;

/**
 * Class Metadata
 * Convert all result that implemented `Arrayable` to array automatically.
 *
 * @method void injectIntoAfter(\Housekeeper\Contracts\Injection\After $injection, $sort = true)
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Metadata
{
    /**
     * Binding injection.
     */
    public function bootMetadata()
    {
        $this->injectIntoAfter(new ToMetadataAfter());
    }
}
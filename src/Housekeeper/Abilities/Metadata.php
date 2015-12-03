<?php

namespace Housekeeper\Abilities;

use Housekeeper\Abilities\Metadata\Injections\ToMetadataAfter;

/**
 * Class Metadata
 *
 * @method void inject(\Housekeeper\Contracts\Injection\Basic $injection, $sortAllInejctions = false)
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Metadata
{
    /**
     * Binding injection.
     */
    public function setupMetadata()
    {
        $this->inject(new ToMetadataAfter());
    }
}
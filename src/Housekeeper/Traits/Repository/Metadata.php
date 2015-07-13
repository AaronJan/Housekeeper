<?php

namespace Housekeeper\Traits\Repository;

use Housekeeper\Injections\Metadata\ToMetadataAfter;
use Housekeeper\Contracts\Injection\InjectionInterface;

/**
 * Class Metadata
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 *
 * @method void inject(InjectionInterface $injection)
 */
trait Metadata
{

    /**
     * Binding injection.
     */
    protected function setupMetadata()
    {
        $this->inject(new ToMetadataAfter());
    }

}
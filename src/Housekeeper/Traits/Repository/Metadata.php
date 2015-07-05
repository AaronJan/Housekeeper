<?php

namespace Housekeeper\Traits\Repository;

use Housekeeper\Injections\Metadata\ToMetadataAfter;

/**
 * Class Metadata
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
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
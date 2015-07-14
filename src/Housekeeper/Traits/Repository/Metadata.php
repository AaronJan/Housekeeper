<?php

namespace Housekeeper\Traits\Repository;

use Housekeeper\Injections\Metadata\ToMetadataAfter;
use Housekeeper\Contracts\Injection\InjectionInterface;

/**
 * Class Metadata
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Metadata
{

    /**
     * @param InjectionInterface $injection
     * @return mixed
     */
    abstract protected function inject(InjectionInterface $injection);


    /**
     * Binding injection.
     */
    protected function setupMetadata()
    {
        $this->inject(new ToMetadataAfter());
    }

}
<?php

namespace Housekeeper\Traits\Repository;

use Housekeeper\Injections\Cacheable\CacheResultAfter;
use Housekeeper\Injections\Cacheable\GetCacheIfExistsBefore;
use Housekeeper\Contracts\Injection\InjectionInterface;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class Cacheable
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Cacheable
{

    /**
     * @param InjectionInterface $injection
     * @return mixed
     */
    abstract protected function inject(InjectionInterface $injection);


    /**
     * Binding caching injections.
     */
    protected function setupCache()
    {
        /**
         * Need Redis support
         */
        try {
            $redis = $this->app->make('redis');
        } catch (\Exception $e) {
            throw new \RuntimeException('Cacheable trait in Housekeeper need Redis support.');
        }

        $this->inject(new GetCacheIfExistsBefore($this->app));
        $this->inject(new CacheResultAfter($this->app));
    }

}
<?php namespace Housekeeper\Traits\Repository;

use Housekeeper\Contracts\Injection\BeforeInjectionInterface;
use Housekeeper\Contracts\Injection\AfterInjectionInterface;
use Housekeeper\Injections\Cacheable\GetCacheIfExistsBefore;
use Housekeeper\Injections\Cacheable\CacheResultAfter;

/**
 * Class Cacheable
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Cacheable
{

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
<?php namespace Housekeeper\Traits\Repository;

use Housekeeper\Exceptions\RepositoryException;
use Housekeeper\Contracts\RepositoryInterface;
use Housekeeper\Contracts\Injection\InjectionInterface;
use Housekeeper\Contracts\Injection\BeforeInjectionInterface;
use Housekeeper\Contracts\Injection\AfterInjectionInterface;
use Housekeeper\Contracts\Injection\ResetInjectionInterface;
use Housekeeper\Flows\Before;
use Housekeeper\Flows\After;
use Housekeeper\Flows\Reset;
use Mockery as m;

/**
 * Class AdjustableTest
 *
 * @covers  Housekeeper\Traits\Repository\Adjustable
 * @package Housekeeper
 */
class AdjustableTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    protected function setUp()
    {

    }

    /**
     *
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     *
     */
    public function testExample()
    {
        //Just a placeholder
        $this->assertTrue(true);
    }

}
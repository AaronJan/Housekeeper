<?php

namespace Housekeeper\Abilities;

use Housekeeper\Exceptions\RepositoryException;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectContract;
use Housekeeper\Contracts\Injection\After as AfterInjectionContract;
use Housekeeper\Contracts\Injection\Reset as ResetInjectionContract;
use Housekeeper\Contracts\Flow\Before as BeforeFlowContract;
use Housekeeper\Contracts\Flow\After as AfterFlowContract;
use Housekeeper\Contracts\Flow\Reset as ResetFlowContract;
use Housekeeper\Flows\Before;
use Housekeeper\Flows\After;
use Housekeeper\Flows\Reset;
use Mockery as m;

/**
 * Class AdjustableTest
 *
 * @covers  Housekeeper\Abilities\Adjustable
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
    
    public function testAssertion()
    {
        $this->assertEquals(1, 1);
    }
    
    
}
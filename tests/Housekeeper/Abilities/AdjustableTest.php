<?php

namespace Housekeeper\Abilities;

use Housekeeper\Exceptions\RepositoryException;
use Housekeeper\Contracts\Repository;
use Housekeeper\Contracts\Injection\Basic;
use Housekeeper\Contracts\Injection\Before;
use Housekeeper\Contracts\Injection\After;
use Housekeeper\Contracts\Injection\Reset;
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

}
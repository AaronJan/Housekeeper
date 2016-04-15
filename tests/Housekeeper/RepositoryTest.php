<?php namespace Housekeeper;

use Housekeeper\Contracts\Action;
use Housekeeper\Contracts\Repository as RepositoryContract;
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
 * Class RepositoryTest
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Eloquent
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
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
     * @runInSeparateProcess
     * @covers Housekeeper\Repository::setApp
     * @covers Housekeeper\Repository::getApp
     */
    public function testSetAppAndGetApp()
    {
        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, true);

        $mockApp = $this->makeMockApplication();

        $methodSetApp = getUnaccessibleObjectMethod($mockRepository, 'setApp');
        $methodGetApp = getUnaccessibleObjectMethod($mockRepository, 'getApp');

        $appFromRepository = $methodGetApp->invoke($mockRepository);
        $this->assertInstanceOf('Illuminate\Contracts\Foundation\Application', $appFromRepository);

        $methodSetApp->invoke($mockRepository, $mockApp);
        $appFromRepositoryLater = $methodGetApp->invoke($mockRepository);
        $this->assertEquals($mockApp, $appFromRepositoryLater);
    }

    /**
     * @runInSeparateProcess
     * @covers Housekeeper\Repository::getConfig
     */
    public function testGetConfig()
    {
        $keyShouldBePassed     = 'aaron';
        $defaultShouldBePassed = 'jan';
        $keyPassed             = false;
        $defaultPassed         = false;

        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        $mockConfig = m::mock('Illuminate\Config\Repository');
        $mockConfig->shouldReceive('get')
            ->andReturnUsing(function ($key, $default) use ($keyShouldBePassed, $defaultShouldBePassed, &$keyPassed, &$defaultPassed) {
                if ($key === $keyShouldBePassed) {
                    $keyPassed = true;
                }

                if ($default === $defaultShouldBePassed) {
                    $defaultPassed = true;
                }
            });

        $mockApplication = m::mock('Illuminate\Contracts\Foundation\Application');
        $mockApplication->shouldReceive('make')
            ->with('config')
            ->andReturn($mockConfig);

        // Set Application instance manually
        $methodSetApp = getUnaccessibleObjectMethod($mockRepository, 'setApp');
        $methodSetApp->invoke($mockRepository, $mockApplication);

        $methodGetConfig = getUnaccessibleObjectMethod($mockRepository, 'getConfig');
        $methodGetConfig->invoke($mockRepository, $keyShouldBePassed, $defaultShouldBePassed);

        $this->assertTrue($keyPassed);
        $this->assertTrue($defaultPassed);
    }

    /**
     * @covers Housekeeper\Repository::newModelInstance
     */
    public function testNewModelInstance()
    {
        $methodModelCalled = false;

        $mockRepository = m::mock(MockSetupRepository::class);
        $mockRepository->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mockRepository->shouldReceive('model')
            ->once()
            ->andReturnUsing(function () use (&$methodModelCalled) {
                $methodModelCalled = true;

                return MockModel::class;
            });

        $methodNewModelInstance = getUnaccessibleObjectMethod($mockRepository, 'newModelInstance');
        $model                  = $methodNewModelInstance->invoke($mockRepository);

        $this->assertInstanceOf(MockModel::class, $model);
        $this->assertTrue($methodModelCalled);

        $model = $methodNewModelInstance->invoke($mockRepository);

        $this->assertInstanceOf(MockModel::class, $model);
    }

    /**
     * @covers Housekeeper\Repository::initialize
     */
    public function testInitializeInNormal()
    {
        $methodNewModelInstanceCalled = false;

        $fakeConfigs = [
            'housekeeper.paginate.per_page' => 10,
        ];

        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        $mockRepository->shouldReceive('newModelInstance')
            ->andReturnUsing(function () use (&$methodNewModelInstanceCalled) {
                $methodNewModelInstanceCalled = true;

                return m::mock('Illuminate\Database\Eloquent\Model');
            });

        $mockRepository->shouldReceive('getConfig')
            ->andReturnUsing(function ($key, $default) use ($fakeConfigs) {
                return $fakeConfigs[$key];
            });

        $methodInitialize = getUnaccessibleObjectMethod($mockRepository, 'initialize');
        $methodInitialize->invoke($mockRepository);

        $attributePerPage = getUnaccessibleObjectPropertyValue($mockRepository, 'perPage');
        $this->assertEquals($fakeConfigs['housekeeper.paginate.per_page'], $attributePerPage);
    }

    /**
     * @covers Housekeeper\Repository::initialize
     * @expectedException \Housekeeper\Exceptions\RepositoryException
     */
    public function testInitializeExpectException()
    {
        $methodNewModelInstanceCalled = false;

        $fakeConfigs = [
            'housekeeper.paginate.per_page' => 10,
        ];

        $mockRepository = m::mock(MockSetupRepository::class);
        $mockRepository->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mockRepository->shouldReceive('newModelInstance')
            ->andReturnUsing(function () use (&$methodNewModelInstanceCalled) {
                $methodNewModelInstanceCalled = true;

                return new \stdClass();
            });

        $mockRepository->shouldReceive('getConfig')
            ->andReturnUsing(function ($key, $default) use ($fakeConfigs) {
                return $fakeConfigs[$key];
            });

        $methodInitialize = getUnaccessibleObjectMethod($mockRepository, 'initialize');
        $methodInitialize->invoke($mockRepository);
    }

    /**
     * @runInSeparateProcess
     * @covers Housekeeper\Repository::callBootable
     */
    public function testCallBootable()
    {
        $methodBootTestOneCalled = false;
        $methodBootTestTwoCalled = false;

        $mockApplication = m::mock('Illuminate\Contracts\Foundation\Application');
        $mockRepository  = $this->makeMockRepository(MockSetupRepository::class, false);

        $mockApplication->shouldReceive('call')
            ->andReturnUsing(function ($callable) use (&$methodBootTestOneCalled, &$methodBootTestTwoCalled) {
                list($object, $methodName) = $callable;

                if ($methodName == 'bootTestOne') {
                    $methodBootTestOneCalled = true;
                } elseif ($methodName == 'bootTestTwo') {
                    $methodBootTestTwoCalled = true;
                }
            });
        $methodSetApp = getUnaccessibleObjectMethod($mockRepository, 'setApp');
        $methodSetApp->invoke($mockRepository, $mockApplication);

        $methodCallBootable = getUnaccessibleObjectMethod($mockRepository, 'callBootable');
        $methodCallBootable->invoke($mockRepository);

        $this->assertTrue($methodBootTestOneCalled);
        $this->assertTrue($methodBootTestTwoCalled);
    }

    /**
     * @runInSeparateProcess
     * @covers Housekeeper\Repository::callBoot
     */
    public function testCallBoot()
    {
        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        $mockApplication = $this->makeMockApplication();
        $mockApplication->shouldReceive('call');

        $mockRepository->shouldReceive('getApp')->andReturn($mockApplication);

        $methodCallBoot = getUnaccessibleObjectMethod($mockRepository, 'callBoot');
        $methodCallBoot->invoke($mockRepository);

        $mockApplication->shouldHaveReceived('call')->withAnyArgs()->once();
    }
    
    /**
     * @runInSeparateProcess
     * @depends testSetAppAndGetApp
     * @depends testCallBoot
     * @depends testCallBootable
     * @depends testInitializeInNormal
     * @depends testInitializeExpectException
     * @covers  Housekeeper\Repository::__construct
     */
    public function testConstruct()
    {
        $methodsWillBeCalledByOrder = [
            'setApp',
            'initialize',
            'callBootable',
            'sortAllInjections',
            'callBoot',
            'reset',
        ];
        $methodsCalledByOrder       = [];

        $mockApplication = $this->makeMockApplication();
        $mockRepository  = $this->makeMockRepository(MockSetupRepository::class, false);

        // `__construct` method will call these methods
        $mockRepository->shouldReceive('setApp')
            ->andReturnUsing(function ($app) use (&$mockApplication, &$methodsCalledByOrder) {
                $method = array_push($methodsCalledByOrder, 'setApp');

                $this->assertEquals($mockApplication, $app);
            });
        $mockRepository->shouldReceive('initialize')
            ->andReturnUsing(function () use (&$methodsCalledByOrder) {
                array_push($methodsCalledByOrder, 'initialize');
            });
        $mockRepository->shouldReceive('callBootable')
            ->andReturnUsing(function () use (&$methodsCalledByOrder) {
                array_push($methodsCalledByOrder, 'callBootable');
            });
        $mockRepository->shouldReceive('sortAllInjections')
            ->andReturnUsing(function () use (&$methodsCalledByOrder) {
                array_push($methodsCalledByOrder, 'sortAllInjections');
            });
        $mockRepository->shouldReceive('callBoot')
            ->andReturnUsing(function () use (&$methodsCalledByOrder) {
                array_push($methodsCalledByOrder, 'callBoot');
            });
        $mockRepository->shouldReceive('reset')
            ->andReturnUsing(function ($action) use (&$methodsCalledByOrder) {
                array_push($methodsCalledByOrder, 'reset');

                $this->assertInstanceOf(Action::class, $action);

                $this->assertEquals(Action::INTERNAL, $action->getType());
            });

        $mockRepository->__construct($mockApplication);

        $this->assertEquals($methodsWillBeCalledByOrder, $methodsCalledByOrder);
    }

    /**
     * @covers Housekeeper\Repository::getKeyName
     */
    public function testGetKeyName()
    {
        /**
         * @var $mockModel m\MockInterface
         */
        $mockRepository = m::mock(MockSetupRepository::class);
        $mockRepository->makePartial()->shouldAllowMockingProtectedMethods();

        $mockModel = $this->makeMockModel();
        $mockModel->shouldReceive('getKeyName')->withNoArgs();

        $mockRepository->shouldReceive('newModelInstance')
            ->andReturnUsing(function () use (&$mockModel) {
                return $mockModel;
            });

        $methodGetKeyName = getUnaccessibleObjectMethod($mockRepository, 'getKeyName');
        $methodGetKeyName->invoke($mockRepository);

        $mockModel->shouldHaveReceived('getKeyName', [])->once();
    }
    
    /**
     * @runInSeparateProcess
     * @covers Housekeeper\Repository::resetPlan
     */
    public function testResetPlan()
    {
        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        // `resetPlan` method will change these properties, these should be `null` now
        $propertyDefaultPlan = getUnaccessibleObjectPropertyValue($mockRepository, 'defaultPlan');
        $propertyPlans       = getUnaccessibleObjectPropertyValue($mockRepository, 'plans');
        $propertyPlanStep    = getUnaccessibleObjectPropertyValue($mockRepository, 'planStep');
        $this->assertNull($propertyDefaultPlan);
        $this->assertNull($propertyPlans);
        $this->assertNull($propertyPlanStep);

        $methodResetPlan = getUnaccessibleObjectMethod($mockRepository, 'resetPlan');
        $methodResetPlan->invoke($mockRepository);

        // properties should be changed
        $propertyDefaultPlan = getUnaccessibleObjectPropertyValue($mockRepository, 'defaultPlan');
        $this->assertInstanceOf(\Housekeeper\Plan::class, $propertyDefaultPlan);

        $propertyPlans = getUnaccessibleObjectPropertyValue($mockRepository, 'plans');
        $this->assertInternalType('array', $propertyPlans);
        $this->assertEmpty($propertyPlans);

        $propertyPlanStep = getUnaccessibleObjectPropertyValue($mockRepository, 'planStep');
        $this->assertNull($propertyPlanStep);
    }

    /**
     * @covers Housekeeper\Repository::reset
     */
    public function testReset()
    {

    }
    
    /**
     * @covers Housekeeper\Repository::getCurrentPlan
     */
    public function testGetCurrentPlanWhenDefault()
    {
        $randomNumber = mt_rand(1, 1000);

        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        setUnaccessibleObjectPropertyValue($mockRepository, 'defaultPlan', $randomNumber);

        $actualValue = $mockRepository->getCurrentPlan();

        $this->assertEquals($randomNumber, $actualValue);
    }

    /**
     * @covers Housekeeper\Repository::getCurrentPlan
     */
    public function testGetCurrentPlanWhenNotDefault()
    {
        $randomNumber = mt_rand(1, 1000);

        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        setUnaccessibleObjectPropertyValue($mockRepository, 'plans', [$randomNumber]);
        setUnaccessibleObjectPropertyValue($mockRepository, 'planStep', 0);

        $actualValue = $mockRepository->getCurrentPlan();

        $this->assertEquals($randomNumber, $actualValue);
    }

    /**
     * @covers Housekeeper\Repository::dropPlan
     */
    public function testDropPlan()
    {
        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        setUnaccessibleObjectPropertyValue($mockRepository, 'plans', [1]);
        $propertyPlans = getUnaccessibleObjectPropertyValue($mockRepository, 'plans');
        $this->assertEquals([1], $propertyPlans);

        // Execute `dropPlan` to remove the value setted before
        $methodDropPlan = getUnaccessibleObjectMethod($mockRepository, 'dropPlan');
        $methodDropPlan->invoke($mockRepository, 0);

        $propertyPlans = getUnaccessibleObjectPropertyValue($mockRepository, 'plans');
        $this->assertEquals([], $propertyPlans);
    }

    /**
     * @covers Housekeeper\Repository::newPlan
     */
    public function testNewPlanWhenHaveDefault()
    {
        $expectDefaultPlan = mt_rand(1, 1000);

        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        setUnaccessibleObjectPropertyValue($mockRepository, 'defaultPlan', $expectDefaultPlan);
        setUnaccessibleObjectPropertyValue($mockRepository, 'plans', []);

        $methodNewPlan = getUnaccessibleObjectMethod($mockRepository, 'newPlan');
        $offset        = $methodNewPlan->invoke($mockRepository);

        $propertyPlanStep    = getUnaccessibleObjectPropertyValue($mockRepository, 'planStep');
        $propertyDefaultPlan = getUnaccessibleObjectPropertyValue($mockRepository, 'defaultPlan');
        $propertyPlans       = getUnaccessibleObjectPropertyValue($mockRepository, 'plans');

        $this->assertEquals(0, $offset);
        $this->assertEquals(0, $propertyPlanStep);
        $this->assertNull($propertyDefaultPlan);
        $this->assertEquals([$expectDefaultPlan], $propertyPlans);
    }

    /**
     * @runInSeparateProcess
     * @covers Housekeeper\Repository::newPlan
     */
    public function testNewPlanWhenDoNotHaveDefault()
    {
        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        setUnaccessibleObjectPropertyValue($mockRepository, 'defaultPlan', null);
        setUnaccessibleObjectPropertyValue($mockRepository, 'plans', [1]);
        setUnaccessibleObjectPropertyValue($mockRepository, 'planStep', 0);

        $methodNewPlan = getUnaccessibleObjectMethod($mockRepository, 'newPlan');
        $offset        = $methodNewPlan->invoke($mockRepository);

        $propertyPlanStep = getUnaccessibleObjectPropertyValue($mockRepository, 'planStep');
        $propertyPlans    = getUnaccessibleObjectPropertyValue($mockRepository, 'plans');

        $this->assertEquals(1, $offset);
        $this->assertEquals(1, $propertyPlanStep);
        $this->assertCount(2, $propertyPlans);

        $plan = $propertyPlans[1];
        $this->assertInstanceOf(\Housekeeper\Plan::class, $plan);
    }

    /**
     * @covers Housekeeper\Repository::getModel
     */
    public function testGetModel()
    {
        $mockPlan = m::mock();
        $mockPlan->shouldReceive('getModel')
            ->andReturn('');

        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        $mockRepository->shouldReceive('getCurrentPlan')
            ->andReturn($mockPlan);

        $methodGetModel = getUnaccessibleObjectMethod($mockRepository, 'getModel');
        $methodGetModel->invoke($mockRepository);

        $mockPlan->shouldHaveReceived('getModel')->once();
    }

    /**
     * @covers Housekeeper\Repository::before
     */
    public function testBefore()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::after
     */
    public function testAfter()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::wrap
     */
    public function testWrap()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::getMethodNameofCallable
     */
    public function testGetMethodNameOfCallable()
    {

    }

    /**
     * @covers Housekeeper\Repository::simpleWrap
     */
    public function testSimpleWrap()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::traceToRealMethod
     */
    public function testTraceToRealMethod()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::whereAre
     * @covers Housekeeper\Repository::applyWheres
     */
    public function testWhereAre()
    {

    }

    /**
     * @covers Housekeeper\Repository::orderBy
     * @covers Housekeeper\Repository::applyOrderBy
     */
    public function testOrderBy()
    {

    }

    /**
     * @covers Housekeeper\Repository::with
     */
    public function testWith()
    {

    }

    /**
     * @covers Housekeeper\Repository::offset
     */
    public function testOffset()
    {

    }
    
    /**
     * @covers Housekeeper\Repository::limit
     */
    public function testLimit()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::exists
     */
    public function testExists()
    {

    }

    /**
     * @covers Housekeeper\Repository::find
     * @covers Housekeeper\Repository::_find
     */
    public function testFind()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::findMany
     * @covers Housekeeper\Repository::_findMany
     */
    public function testFindMany()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::create
     * @covers Housekeeper\Repository::_create
     */
    public function testCreate()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::delete
     * @covers Housekeeper\Repository::_delete
     */
    public function testDelete()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::update
     * @covers Housekeeper\Repository::_update
     */
    public function testUpdate()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::all
     * @covers Housekeeper\Repository::_all
     */
    public function testAll()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::paginate
     * @covers Housekeeper\Repository::_paginate
     */
    public function testPaginate()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::getByField
     * @covers Housekeeper\Repository::_getByField
     */
    public function testGetByField()
    {
        
    }

    /**
     * @covers Housekeeper\Repository::findByField
     * @covers Housekeeper\_findByField::_firstOrFailByField
     */
    public function testFindByField()
    {

    }
    
    /**
     * Internal method calling should creating different layer to isolate
     * outcome from each other.
     */
    public function testIsolationWhenCallingInternalMethod()
    {

    }


    // ========================================================================

    /**
     * @param string $class
     * @param bool   $concrete
     * @return Repository|m\MockInterface
     */
    protected function makeMockRepository($class = 'Housekeeper\Repository',
                                          $concrete = true)
    {
        /**
         * Setup some hints for variables.
         *
         * @var \Housekeeper\Repository|\Mockery\MockInterface $mockRepository
         */

        /**
         * Mock Repository
         */
        $mockRepository = m::mock($class);
        $mockRepository->makePartial()
            ->shouldAllowMockingProtectedMethods();

        /**
         * Override "newModelInstance" method, returns a mock model.
         */
        $mockRepository->shouldReceive('newModelInstance')
            ->andReturnUsing(function () {
                return $this->makeMockModel();
            });

        /**
         * Once we mocked "makeModel" method, we can safely Re-concreting
         * Repository object.
         */
        if ($concrete) {
            $mockRepository->__construct($this->makeMockApplication());
        }

        return $mockRepository;
    }

    /**
     * @return m\MockInterface|\Illuminate\Contracts\Foundation\Application
     */
    protected function makeMockApplication()
    {
        /**
         * Mock "Config" instance.
         */
        $mockConfig = m::mock('Illuminate\Config\Repository');

        $mockConfig->shouldReceive('get')
            ->andReturnNull();

        /**
         * Mock "Application".
         */
        $mockApplication = m::mock('Illuminate\Contracts\Foundation\Application');

        $mockApplication->shouldReceive('config')
            ->andReturn([]);

        $mockApplication->shouldReceive('make')
            ->with('config')
            ->andReturn($mockConfig);

        $mockApplication->shouldReceive('make')
            ->with()
            ->andReturn([]);

        $mockApplication->shouldReceive('call')
            ->andReturn([]);

        return $mockApplication;
    }

    /**
     * @param string $class
     * @return m\MockInterface
     */
    protected function makeMockModel($class = 'Illuminate\Database\Eloquent\Model')
    {
        $mock = m::mock($class);

        $mock->shouldReceive('get')
            ->andReturn(m::mock('Illuminate\Database\Eloquent\Collection'));

        return $mock;
    }

    /**
     * @return m\MockInterface
     */
    protected function makeMockAction()
    {
        $mock = m::mock('Housekeeper\Action');

        $mock->shouldReceive('getArguments')
            ->andReturn([]);

        $mock->shouldReceive('getMethodName')
            ->andReturn('fake');

        return $mock;
    }

}

// ============================================================================

/**
 * Class MockBasicInjection
 *
 * @package Housekeeper
 */
class MockBasicInjection implements BasicInjectionContract

{
    /**
     * @return int
     */
    public function priority()
    {
        return 1;
    }
}

/**
 * Class MockSetupRepository
 *
 * @package Housekeeper
 */
class MockSetupRepository extends Repository
{
    /**
     *
     */
    protected function model()
    {

    }
    
    public function boot()
    {

    }

    /**
     *
     */
    public function bootTestOne()
    {
        $mockInjection = new MockBasicInjection();

        $this->inject($mockInjection);
    }

    /**
     *
     */
    public function bootTestTwo()
    {

    }

    /**
     * @param \Housekeeper\Contracts\Injection\Basic $a
     * @param \Housekeeper\Contracts\Injection\Basic $b
     * @return int
     */
    protected static function sortInjection(\Housekeeper\Contracts\Injection\Basic $a, \Housekeeper\Contracts\Injection\Basic $b)
    {
        return 0;
    }
}

/**
 * Class MockModel
 *
 * @package Housekeeper
 */
class MockModel
{

}
<?php namespace Housekeeper;

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
 * @runTestsInSeparateProcesses
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
    public function testInitialize()
    {
        $methodNewModelInstanceCalled = false;

        $fakeModelClass = 'FakeModel';
        $fakeConfigs    = [
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
     * Housekeeper\Repository::sortAllInjections
     */
    public function testSortAllInjections()
    {
        // mock Injections for Repository to sort
        $mockInjections = [
            'reset'  => [],
            'before' => [],
            'after'  => [],
        ];
        $priorities = [
            3, 3, 4, 5, 1,
            2, 1, 5, 4, 6,
            100, 1, 99, 2, 66,
        ];
        foreach (['reset', 'before', 'after'] as $group) {
            for ($i = 0; $i < 5; $i ++) {
                $priority = array_pop($priorities);

                $mockInjection = m::mock(MockBasicInjection::class);
                $mockInjection->shouldReceive('priority')
                    ->andReturnUsing(function () use ($priority) {
                        return $priority;
                    });

                $mockInjections[$group][] = $mockInjection;
            }
        }

        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        // Mock `sortInjection` method
        $methodSortInjection = getUnaccessibleObjectMethod($mockRepository, 'sortInjection');
        $mockRepository->shouldReceive('sortInjection')
            ->andReturnUsing(function ($a, $b) use ($methodSortInjection, $mockRepository) {
                return $methodSortInjection->invoke($mockRepository, $a, $b);
            });

        // Replace `injections` property with mock `injections`
        $mockRepositoryReflection     = new \ReflectionClass($mockRepository);
        $propertyInjectionsReflection = $mockRepositoryReflection->getProperty('injections');
        $propertyInjectionsReflection->setAccessible(true);
        $propertyInjectionsReflection->setValue($mockRepository, $mockInjections);

        // Execute `sortAllInjections` method
        $methodSortAllInjections = getUnaccessibleObjectMethod($mockRepository, 'sortAllInjections');
        $methodSortAllInjections->invoke($mockRepository);

        // Verify results
        $sortedInjections = $propertyInjectionsReflection->getValue($mockRepository);
        foreach ($sortedInjections as $group) {
            $lastPriority = - 1;

            foreach ($group as $injection) {
                // ascend
                $this->assertGreaterThanOrEqual($lastPriority, $injection->priority());
            }
        }
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
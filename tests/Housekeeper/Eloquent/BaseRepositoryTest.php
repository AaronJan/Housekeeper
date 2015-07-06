<?php namespace Housekeeper\Eloquent;

use Housekeeper\Exceptions\RepositoryException;
use Housekeeper\Contracts\RepositoryInterface;
use Housekeeper\Contracts\Injection\InjectionInterface;
use Housekeeper\Contracts\Injection\BeforeInjectionInterface;
use Housekeeper\Contracts\Injection\AfterInjectionInterface;
use Housekeeper\Contracts\Injection\ResetInjectionInterface;
use Housekeeper\Flow\Before;
use Housekeeper\Flow\After;
use Housekeeper\Flow\Reset;
use Mockery as m;

/**
 * Class BaseRepositoryTest
 *
 * @package Housekeeper\Eloquent
 */
class BaseRepositoryTest extends \PHPUnit_Framework_TestCase
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
     * @covers Housekeeper\Eloquent\BaseRepository::setup
     */
    public function testSetup()
    {
        /**
         * Mock custom Repository class but do not call "__construct" yet,
         * because "setup" method will be called.
         */
        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);

        /**
         * Mock a method named "setupTest", when repository initializing, it'll
         * call "setup" method, and "setup" method will call all method that
         * name starting with "setup", so If "setupTest" be called, then
         * "$called" should be true.
         */
        $called = false;
        $mockRepository->shouldReceive('setupTest')
            ->andReturnUsing(function () use (&$called) {
                $called = true;
            });

        /**
         * Call "setup" function to verify that.
         */
        $methodSetup = $this->getUnaccessibleObjectMethod($mockRepository, 'setup');
        $methodSetup->invoke($mockRepository, array());

        $this->assertTrue($called);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::modelInstance
     */
    public function testModelInstance()
    {
        $mockRepository = m::mock(BaseRepository::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();;

        /**
         * Because in the "model" method we only return a model class name, so
         * we should mock it as hard-dependency.
         */
        $mockModel = $this->makeMockModel('overload:Test\FakeModel');

        /**
         * So in here, we just return the class name of mock model.
         */
        $mockRepository->shouldReceive('model')
            ->andReturn('Test\FakeModel');

        /**
         * Check it.
         */
        $methodModelInstance = $this->getUnaccessibleObjectMethod($mockRepository, 'modelInstance');
        $model               = $methodModelInstance->invoke($mockRepository, array());

        $this->assertInstanceOf('Test\FakeModel', $model);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::freshModel
     */
    public function testFreshModel()
    {
        $mockRepository = $this->makeMockRepository(BaseRepository::class, false);

        /**
         * The model instance should be "null" at first.
         */
        $model = $this->getUnaccessibleObjectPropertyValue($mockRepository, 'model');

        $this->assertNull($model);

        /**
         * Call "freshModel" method to generate a new Model.
         */
        $methodFreshModel = $this->getUnaccessibleObjectMethod($mockRepository, 'freshModel');
        $methodFreshModel->invoke($mockRepository, array());

        /**
         * Check it.
         */
        $model = $this->getUnaccessibleObjectPropertyValue($mockRepository, 'model');

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::inject
     * @covers Housekeeper\Eloquent\BaseRepository::sortAllInjections
     * @covers Housekeeper\Eloquent\BaseRepository::sortInjection
     */
    public function testInjectResetFlow()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * Mock an injection that implements ResetInjectionInterface.
         */
        $resetInjection = m::mock(
            InjectionInterface::class,
            ResetInjectionInterface::class
        );
        $resetInjection->shouldReceive('priority')
            ->andReturn(1);

        /**
         * Call "inject" function, this injection should goes to "reset"
         * injections.
         */
        $methodInject = $this->getUnaccessibleObjectMethod($mockRepository, 'inject');
        $methodInject->invoke($mockRepository, $resetInjection);

        /**
         * Get all injections and check them.
         */
        $injections = $this->getUnaccessibleObjectPropertyValue($mockRepository, 'injections');

        $this->assertCount(1, $injections['reset']);
        $this->assertEquals($resetInjection, $injections['reset'][0]);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::inject
     * @covers Housekeeper\Eloquent\BaseRepository::sortAllInjections
     * @covers Housekeeper\Eloquent\BaseRepository::sortInjection
     */
    public function testInjectBeforeFlow()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * Mock an injection that implements AfterInjectionInterface.
         */
        $beforeInjection = m::mock(
            InjectionInterface::class,
            BeforeInjectionInterface::class
        );
        $beforeInjection->shouldReceive('priority')
            ->andReturn(1);

        /**
         * Call "inject" function, this injection should goes to "before"
         * injections.
         */
        $methodInject = $this->getUnaccessibleObjectMethod($mockRepository, 'inject');
        $methodInject->invoke($mockRepository, $beforeInjection);

        /**
         * Get all injections and check them.
         */
        $injections = $this->getUnaccessibleObjectPropertyValue($mockRepository, 'injections');

        $this->assertCount(1, $injections['before']);
        $this->assertEquals($beforeInjection, $injections['before'][0]);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::inject
     * @covers Housekeeper\Eloquent\BaseRepository::sortAllInjections
     * @covers Housekeeper\Eloquent\BaseRepository::sortInjection
     */
    public function testInjectAfterFlow()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * Mock an injection that implements AfterInjectionInterface.
         */
        $afterInjection = m::mock(
            InjectionInterface::class,
            AfterInjectionInterface::class
        );
        $afterInjection->shouldReceive('priority')
            ->andReturn(1);

        /**
         * Call "inject" function, this injection should goes to "after"
         * injections.
         */
        $methodInject = $this->getUnaccessibleObjectMethod($mockRepository, 'inject');
        $methodInject->invoke($mockRepository, $afterInjection);

        /**
         * Get all injections and check them.
         */
        $injections = $this->getUnaccessibleObjectPropertyValue($mockRepository, 'injections');

        $this->assertCount(1, $injections['after']);
        $this->assertEquals($afterInjection, $injections['after'][0]);
    }

    /**
     * @expectedException \Housekeeper\Exceptions\RepositoryException
     */
    public function testInjectException()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * Mock an injection that implements basic InjectionInterface, but
         * without any real process injection interface, so it should makes an
         * exception.
         */
        $uselessInjection = m::mock(
            InjectionInterface::class
        );
        $uselessInjection->shouldReceive('priority')
            ->andReturn(1);

        /**
         * Call "inject" function to verify that.
         */
        $methodInject = $this->getUnaccessibleObjectMethod($mockRepository, 'inject');
        $methodInject->invoke($mockRepository, $uselessInjection);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::reset
     */
    public function testReset()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * These should be "true" after tests completed.
         */
        $freshModelCalled = false;
        $injectionCalled  = false;

        /**
         * Mock a reset injection.
         */
        $resetInjection = m::mock(
            InjectionInterface::class,
            ResetInjectionInterface::class
        );
        $resetInjection->shouldReceive('priority')
            ->andReturn(1);
        $resetInjection->shouldReceive('handle')
            ->andReturnUsing(function () use (&$injectionCalled) {
                $injectionCalled = true;
            });

        /**
         * When "reset" be called,  it should call "freshModel" function.
         */
        $mockRepository->shouldReceive('freshModel')
            ->andReturnUsing(function () use (&$freshModelCalled) {
                $freshModelCalled = true;
            });

        /**
         * Bind reset event handler directly.
         */
        $this->setUnaccessibleObjectPropertyValue(
            $mockRepository,
            'injections',
            [
                'reset'  => [$resetInjection],
                'before' => [],
                'after'  => []
            ]
        );

        /**
         * Mock an Action.
         */
        $mockAction = $this->makeMockAction();

        //Call "reset"
        $mockRepository->reset($mockAction);

        $this->assertTrue($injectionCalled);
        $this->assertTrue($freshModelCalled);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::before
     */
    public function testBefore()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * These should be "true" after tests completed.
         */
        $injectionCalled = false;

        /**
         * Mock a before injection.
         */
        $beforeInjection = m::mock(
            InjectionInterface::class,
            BeforeInjectionInterface::class
        );
        $beforeInjection->shouldReceive('priority')
            ->andReturn(1);
        $beforeInjection->shouldReceive('handle')
            ->andReturnUsing(function ($flow) use (&$injectionCalled) {
                $injectionCalled = true;
            });

        /**
         * Bind before injection directly.
         */
        $this->setUnaccessibleObjectPropertyValue(
            $mockRepository,
            'injections',
            [
                'reset'  => [],
                'before' => [$beforeInjection],
                'after'  => []
            ]
        );

        /**
         * Mock an Action.
         */
        $mockAction = $this->makeMockAction();

        //Call "before"
        $methodBefore = $this->getUnaccessibleObjectMethod($mockRepository, 'before');
        $methodBefore->invoke($mockRepository, $mockAction);

        $this->assertTrue($injectionCalled);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::after
     */
    public function testAfter()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * These should be "true" after tests completed.
         */
        $InjectionCalled = false;

        /**
         * Mock a after injection.
         */
        $afterInjection = m::mock(
            InjectionInterface::class,
            AfterInjectionInterface::class
        );
        $afterInjection->shouldReceive('priority')
            ->andReturn(1);
        $afterInjection->shouldReceive('handle')
            ->andReturnUsing(function () use (&$InjectionCalled) {
                $InjectionCalled = true;
            });

        /**
         * Bind after injection directly.
         */
        $this->setUnaccessibleObjectPropertyValue(
            $mockRepository,
            'injections',
            [
                'reset'  => [],
                'before' => [],
                'after'  => [$afterInjection]
            ]
        );

        /**
         * Mock an Action.
         */
        $mockAction = $this->makeMockAction();

        //Call "after"
        $methodAfter = $this->getUnaccessibleObjectMethod($mockRepository, 'after');
        $methodAfter->invoke($mockRepository, $mockAction, '');

        $this->assertTrue($InjectionCalled);
    }

    /**
     * @coversNothing
     */
    public function testWhenBeforeHasReturn()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * These should be "true" after tests completed.
         */
        $closureCalled      = false;
        $beforeMethodCalled = false;
        $afterMethodCalled  = false;
        $resetMethodCalled  = false;

        $closure = function () use (&$closureCalled) {
            $closureCalled = true;
        };

        $mockRepository->shouldReceive('before')
            ->andReturnUsing(function () use (&$beforeMethodCalled) {
                $beforeMethodCalled = true;

                $mockFlow = m::mock('Housekeeper\Flow\Before');
                $mockFlow->shouldReceive('hasReturn')
                    ->andReturn(true);
                $mockFlow->shouldReceive('getReturn')
                    ->andReturn('good');
                $mockFlow->shouldReceive('handle')
                    ->andReturnNull();

                return $mockFlow;
            });

        $mockRepository->shouldReceive('after')
            ->andReturnUsing(function ($methodName, $result) use (&$afterMethodCalled) {
                $afterMethodCalled = true;

                $mockFlow = m::mock('Housekeeper\Flow\After');
                $mockFlow->shouldReceive('getReturn')
                    ->andReturn('bad');
                $mockFlow->shouldReceive('handle')
                    ->andReturnNull();

                return $mockFlow;
            });

        $mockRepository->shouldReceive('reset')
            ->andReturnUsing(function () use (&$resetMethodCalled) {
                $resetMethodCalled = true;
            });

        /**
         * Mock an Action.
         */
        $mockAction = $this->makeMockAction();

        /**
         * Call "wrap" method.
         */
        $methodWrap = $this->getUnaccessibleObjectMethod($mockRepository, 'wrap');
        $result     = $methodWrap->invoke($mockRepository, $closure, $mockAction);

        $this->assertFalse($closureCalled);
        $this->assertTrue($beforeMethodCalled);
        $this->assertFalse($afterMethodCalled);
        $this->assertFalse($resetMethodCalled);

        $this->assertEquals('good', $result);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::wrap
     */
    public function testWrap()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * These should be "true" after tests completed.
         */
        $closureCalled      = false;
        $beforeMethodCalled = false;
        $afterMethodCalled  = false;
        $resetMethodCalled  = false;

        $closure = function () use (&$closureCalled) {
            $closureCalled = true;

            return 1;
        };

        $mockRepository->shouldReceive('before')
            ->andReturnUsing(function () use (&$beforeMethodCalled) {
                $beforeMethodCalled = true;

                $mockFlow = m::mock('Housekeeper\Flow\Before');
                $mockFlow->shouldReceive('hasReturn')
                    ->andReturn(false);
                $mockFlow->shouldReceive('handle')
                    ->andReturnNull();

                return $mockFlow;
            });

        $mockRepository->shouldReceive('after')
            ->andReturnUsing(function ($methodName, $result) use (&$afterMethodCalled) {
                $afterMethodCalled = true;

                $mockFlow = m::mock('Housekeeper\Flow\After');
                $mockFlow->shouldReceive('getReturn')
                    ->andReturn($result . 'good');
                $mockFlow->shouldReceive('handle')
                    ->andReturnNull();

                return $mockFlow;
            });

        $mockRepository->shouldReceive('reset')
            ->andReturnUsing(function () use (&$resetMethodCalled) {
                $resetMethodCalled = true;
            });

        /**
         * Mock an Action.
         */
        $mockAction = $this->makeMockAction();

        /**
         * Call "wrap" method.
         */
        $methodWrap = $this->getUnaccessibleObjectMethod($mockRepository, 'wrap');
        $result     = $methodWrap->invoke($mockRepository, $closure, $mockAction);

        $this->assertTrue($closureCalled);
        $this->assertTrue($beforeMethodCalled);
        $this->assertTrue($afterMethodCalled);
        $this->assertTrue($resetMethodCalled);

        $this->assertEquals('1good', $result);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::sortInjection
     */
    public function testSortInjection()
    {
        $mockRepository = $this->makeMockRepository();

        /**
         * Mock three injections to test sorting, equal, lesser and greater.
         */
        $mockInjection_1 = m::mock('Housekeeper\Contracts\Injection\InjectionInterface');
        $mockInjection_1->shouldReceive('priority')
            ->andReturn('1');

        $mockInjection_2 = m::mock('Housekeeper\Contracts\Injection\InjectionInterface');
        $mockInjection_2->shouldReceive('priority')
            ->andReturn('2');

        $mockInjection_3 = m::mock('Housekeeper\Contracts\Injection\InjectionInterface');
        $mockInjection_3->shouldReceive('priority')
            ->andReturn('1');

        /**
         * Get the "sort" method.
         */
        $methodSortInjection = $this->getUnaccessibleObjectMethod($mockRepository, 'sortInjection');

        $this->assertEquals(
            -1,
            $methodSortInjection->invoke($mockRepository, $mockInjection_1, $mockInjection_2)
        );

        $this->assertEquals(
            1,
            $methodSortInjection->invoke($mockRepository, $mockInjection_2, $mockInjection_1)
        );

        $this->assertEquals(
            0,
            $methodSortInjection->invoke($mockRepository, $mockInjection_1, $mockInjection_1)
        );
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::addCondition
     * @covers Housekeeper\Eloquent\BaseRepository::getConditions
     */
    public function testConditions()
    {
        $mockRepository = $this->makeMockRepository();

        $mockRepository->addCondition('where', [
            'name' => 'Aaron',
        ]);

        $mockRepository->addCondition('with', [
            'article',
        ]);

        $conditions = $mockRepository->getConditions();

        $this->assertEquals([
            [
                'where' => [
                    'name' => 'Aaron',
                ]
            ],
            [
                'with' => [
                    'article'
                ]
            ],
        ], $conditions);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::all
     */
    public function testAll()
    {
        $mockRepository = $this->makeMockRepository();

        $result = $mockRepository->all();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $result);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::find
     */
    public function testFind()
    {
        /**
         * Hand-mock a repository, then we pass it customized model specific for
         * this test.
         */
        $mockRepository = m::mock('Housekeeper\Eloquent\BaseRepository');
        $mockRepository->makePartial()
            ->shouldAllowMockingProtectedMethods();

        /**
         * Mock a customize model.
         */
        $mockModel = $this->makeMockModel();

        $mockModel->shouldReceive('findOrFail')
            ->with(3, ['*'])
            ->andReturn(m::mock('Illuminate\Database\Eloquent\Model'));

        /**
         * Inject the mock model.
         */
        $mockRepository->shouldReceive('modelInstance')
            ->andReturnUsing(function () use ($mockModel) {
                return $mockModel;
            });

        /**
         * Call the constructor of mock repository.
         */
        $mockRepository->__construct($this->mockApplication());

        /**
         * Check "paginate".
         */
        $result = $mockRepository->find(3);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $result);
    }

    /**
     * @covers Housekeeper\Eloquent\BaseRepository::paginate
     */
    public function testPaginate()
    {
        /**
         * Hand-mock a repository, then we pass it customized model specific for
         * this test.
         */
        $mockRepository = m::mock('Housekeeper\Eloquent\BaseRepository');
        $mockRepository->makePartial()
            ->shouldAllowMockingProtectedMethods();

        /**
         * Mock a customize model.
         */
        $mockModel = $this->makeMockModel();

        $mockModel->shouldReceive('paginate')
            ->with(15, ['*'])
            ->andReturn(m::mock('Illuminate\Contracts\Pagination\LengthAwarePaginator'));

        /**
         * Inject the mock model.
         */
        $mockRepository->shouldReceive('modelInstance')
            ->andReturnUsing(function () use ($mockModel) {
                return $mockModel;
            });

        /**
         * Call the constructor of mock repository.
         */
        $mockRepository->__construct($this->mockApplication());

        /**
         * Check "paginate".
         */
        $result = $mockRepository->paginate(15);

        $this->assertInstanceOf('Illuminate\Contracts\Pagination\LengthAwarePaginator', $result);
    }


    // ========================================================================

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setUnaccessibleObjectPropertyValue($object, $property, $value)
    {
        $relection = new \ReflectionClass($object);

        $reflectionProperty = $relection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * @param        $object
     * @param string $property
     * @return mixed
     */
    protected function getUnaccessibleObjectPropertyValue($object, $property)
    {
        $relection = new \ReflectionClass($object);

        $reflectionProperty = $relection->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * @param $object
     * @param $methodName
     * @return \ReflectionMethod
     */
    protected function getUnaccessibleObjectMethod($object, $methodName)
    {
        $relection = new \ReflectionClass($object);
        $method    = $relection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param string $class
     * @param bool   $concrete
     * @return BaseRepository|m\MockInterface
     */
    protected function makeMockRepository($class = 'Housekeeper\Eloquent\BaseRepository', $concrete = true)
    {
        /**
         * Setup some hints for variables.
         *
         * @var \Housekeeper\Eloquent\BaseRepository|\Mockery\MockInterface $mockRepository
         */

        /**
         * Mock Repository
         */
        $mockRepository = m::mock($class);
        $mockRepository->makePartial()
            ->shouldAllowMockingProtectedMethods();

        /**
         * Override "makeModelInstance" method, returns a mock model.
         */
        $mockRepository->shouldReceive('modelInstance')
            ->andReturnUsing(function () {
                return $this->makeMockModel();
            });

        /**
         * Once we mocked "makeModel" method, we can safely Re-concreting
         * Repository object.
         */
        if ($concrete) {
            $mockRepository->__construct($this->mockApplication());
        }

        return $mockRepository;
    }

    /**
     * @return m\MockInterface|\Illuminate\Contracts\Foundation\Application
     */
    protected function mockApplication()
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

class MockInjection implements InjectionInterface, ResetInjectionInterface

{

    public function priority()
    {
        return 1;
    }

    public function handle(Reset $flow)
    {

    }

}

/**
 * Class MockSetupRepository
 *
 * @package Housekeeper\Eloquent
 */
class MockSetupRepository extends BaseRepository
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
    protected function setupTest()
    {
        $mockInjection = new MockInjection();

        $this->inject($mockInjection);
    }

}
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
 * @covers  Housekeeper\Repository
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

        $mockApp = $this->mockApplication();

        $methodSetApp = getUnaccessibleObjectMethod($mockRepository, 'setApp');
        $methodGetApp = getUnaccessibleObjectMethod($mockRepository, 'getApp');

        $appFromRepository = $methodGetApp->invoke($mockRepository);
        $this->assertInstanceOf('Illuminate\Contracts\Foundation\Application', $appFromRepository);

        $methodSetApp->invoke($mockRepository, $mockApp);
        $appFromRepositoryLater = $methodGetApp->invoke($mockRepository);
        $this->assertEquals($mockApp, $appFromRepositoryLater);
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

/**
 * Class MockBasicInjection
 *
 * @package Housekeeper
 */
class MockBasicInjection implements ResetInjectionContract

{

    public function priority()
    {
        return 1;
    }

    public function handle(ResetFlowContract $flow)
    {

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
    protected function setupTest()
    {
        $mockInjection = new MockBasicInjection();

        $this->inject($mockInjection);
    }
}
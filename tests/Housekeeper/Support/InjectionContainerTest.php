<?php namespace Housekeeper\Support;

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
class InjectionContainerTest extends \PHPUnit_Framework_TestCase
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

//    
//    /**
//     * @runInSeparateProcess
//     * @covers Housekeeper\Repository::sortAllInjections
//     * @covers Housekeeper\Repository::sortInjection
//     */
//    public function testSortAllInjections()
//    {
//        // mock Injections for Repository to sort
//        $mockInjections = $this->makeMockInjections();
//
//        $mockRepository = $this->makeMockRepository(MockSetupRepository::class, false);
//
//        // Mock `sortInjection` method (same as the original method) because it's private
//        $methodSortInjection = getUnaccessibleObjectMethod($mockRepository, 'sortInjection');
//        $mockRepository->shouldReceive('sortInjection')
//            ->andReturnUsing(function ($a, $b) use ($methodSortInjection, $mockRepository) {
//                return $methodSortInjection->invoke($mockRepository, $a, $b);
//            });
//
//        // Replace `injections` property with mock `injections`
//        $mockRepositoryReflection     = new \ReflectionClass($mockRepository);
//        $propertyInjectionsReflection = $mockRepositoryReflection->getProperty('injections');
//        $propertyInjectionsReflection->setAccessible(true);
//        $propertyInjectionsReflection->setValue($mockRepository, $mockInjections);
//
//        // Execute `sortAllInjections` method
//        $methodSortAllInjections = getUnaccessibleObjectMethod($mockRepository, 'sortAllInjections');
//        $methodSortAllInjections->invoke($mockRepository);
//
//        // Verify results
//        $sortedInjections = $propertyInjectionsReflection->getValue($mockRepository);
//        foreach ($sortedInjections as $group) {
//            $lastPriority = - 1;
//
//            foreach ($group as $injection) {
//                // ascending order
//                $this->assertGreaterThanOrEqual($lastPriority, $injection->priority());
//            }
//        }
//    }
//
//    /**
//     * @return array
//     */
//    protected function makeMockInjections()
//    {
//        $mockInjections = [
//            'reset'  => [],
//            'before' => [],
//            'after'  => [],
//        ];
//        $priorities     = [
//            3, 3, 4, 5, 1,
//            2, 1, 5, 4, 6,
//            100, 1, 99, 2, 66,
//        ];
//        foreach (['reset', 'before', 'after'] as $group) {
//            for ($i = 0; $i < 5; $i ++) {
//                $priority = array_pop($priorities);
//
//                $mockInjection = m::mock(MockBasicInjection::class);
//                $mockInjection->shouldReceive('priority')
//                    ->andReturnUsing(function () use ($priority) {
//                        return $priority;
//                    });
//
//                $mockInjections[$group][] = $mockInjection;
//            }
//        }
//
//        return $mockInjections;
//    }


}

// ============================================================================

<?php namespace Housekeeper\Injections\Adjustable;

use Housekeeper\Contracts\Injection\InjectionInterface;
use Housekeeper\Contracts\Injection\BeforeInjectionInterface;
use Housekeeper\Flow\Before;
use Housekeeper\Traits\Repository\Adjustable;
use Housekeeper\Contracts\RepositoryInterface;

/**
 * Class ApplyCriteriasBefore
 *
 * @priority 10
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Adjustable
 */
class ApplyCriteriasBefore implements InjectionInterface,
                                      BeforeInjectionInterface
{

    /**
     * @return int
     */
    public function priority()
    {
        return 10;
    }

    /**
     * @param Before $flow
     */
    public function handle(Before $flow)
    {
        /**
         * @var RepositoryInterface|Adjustable $repository
         */
        $repository = $flow->getRepository();

        $criterias = $repository->getCriterias();

        array_walk($criterias, function (&$criteria) use (&$repository) {
            /**
             * @var \Housekeeper\Contracts\CriteriaInterface $criteria
             */
            $repository->applyCriteria($criteria);
        });
    }

}
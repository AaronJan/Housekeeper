<?php

namespace Housekeeper\Abilities\Adjustable\Injections;

use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectionContract;
use Housekeeper\Contracts\Flow\Before as BeforeFlowContract;

/**
 * Class ApplyCriteriasBefore
 *
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Injections\Adjustable
 */
class ApplyCriteriasBefore implements BasicInjectionContract,
                                      BeforeInjectionContract
{
    const PRIORITY = 10;


    /**
     * @return int
     */
    public function priority()
    {
        return static::PRIORITY;
    }

    /**
     * @param \Housekeeper\Contracts\Flow\Before $beforeFlow
     */
    public function handle(BeforeFlowContract $beforeFlow)
    {
        /**
         * @var \Housekeeper\Contracts\Repository|\Housekeeper\Abilities\Adjustable $repository
         */
        $repository = $beforeFlow->getRepository();

        $criterias = $repository->getCriterias();

        array_walk($criterias, function ($criteria) use ($repository) {
            /**
             * @var \Housekeeper\Abilities\Adjustable\Contracts\Criteria $criteria
             */
            $repository->applyCriteria($criteria);
        });
    }

}
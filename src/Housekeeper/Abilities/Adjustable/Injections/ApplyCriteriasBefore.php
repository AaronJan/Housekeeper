<?php

namespace Housekeeper\Abilities\Adjustable\Injections;

use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectionContract;
use Housekeeper\Contracts\Flow\Before as BeforeFlowContract;

/**
 * Class ApplyCriteriasBefore
 *
 * @author   AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package  Housekeeper\Abilities\Adjustable\Injections
 */
class ApplyCriteriasBefore implements BasicInjectionContract,
                                      BeforeInjectionContract
{
    /**
     * @var int
     */
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
        // Only affecting the first wrapped method
        if ($beforeFlow->getIndex() != 1) {
            return;
        }

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
<?php

namespace Housekeeper\Abilities;

use Housekeeper\Abilities\Adjustment\Contracts\Criteria;
use Housekeeper\Abilities\Adjustment\Injections\ApplyCriteriasBefore;

/**
 * Class Adjustment
 *
 * @method void inject(\Housekeeper\Contracts\Injection\Basic $injection, $sortAllInejctions = false)
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Adjustment
{
    /**
     * @var \Housekeeper\Abilities\Adjustment\Contracts\Criteria[]
     */
    protected $criterias = [];


    /**
     * Bind injection.
     */
    public function bootAdjustment()
    {
        $this->inject(new ApplyCriteriasBefore());
    }

    /**
     * ave this criteria, it would be auto-applied before every method calling.
     *
     * @param \Housekeeper\Abilities\Adjustment\Contracts\Criteria $criteria
     * @return $this
     */
    public function rememberCriteria(Criteria $criteria)
    {
        $this->criterias[] = $criteria;
        
        return $this;
    }

    /**
     * Remove all criterias that this repository remembered.
     *
     * @return $this
     */
    public function forgetCriterias()
    {
        $this->criterias = [];
        
        return $this;
    }

    /**
     * Get all criterias that this repository remembered.
     *
     * @return array
     */
    public function getCriterias()
    {
        return $this->criterias;
    }

    /**
     * @param \Housekeeper\Abilities\Adjustment\Contracts\Criteria $criteria
     * @return $this
     */
    public function applyCriteria(Criteria $criteria)
    {
        /**
         * @var \Housekeeper\Contracts\Repository $this
         */
        $criteria->apply($this);
        
        return $this;
    }

}
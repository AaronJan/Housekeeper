<?php

namespace Housekeeper\Abilities;

use Housekeeper\Abilities\Adjustable\Contracts\Criteria;
use Housekeeper\Abilities\Adjustable\Injections\ApplyCriteriasBefore;

/**
 * Class Adjustment
 *
 * @method void injectIntoBefore(\Housekeeper\Contracts\Injection\Before $injection, $sort = true)
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Adjustable
{
    /**
     * @var \Housekeeper\Abilities\Adjustable\Contracts\Criteria[]
     */
    protected $criterias = [];


    /**
     * Bind injection.
     */
    public function bootAdjustable()
    {
        $this->injectIntoBefore(new ApplyCriteriasBefore());
    }

    /**
     * ave this criteria, it would be auto-applied before every method calling.
     *
     * @param \Housekeeper\Abilities\Adjustable\Contracts\Criteria $criteria
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
     * @return Criteria[]
     */
    public function getCriterias()
    {
        return $this->criterias;
    }

    /**
     * @param \Housekeeper\Abilities\Adjustable\Contracts\Criteria $criteria
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
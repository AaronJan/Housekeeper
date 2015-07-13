<?php

namespace Housekeeper\Traits\Repository;

use Housekeeper\Contracts\Injection\BeforeInjectionInterface;
use Housekeeper\Injections\Adjustable\ApplyCriteriasBefore;
use Housekeeper\Contracts\Injection\InjectionInterface;
use Housekeeper\Contracts\CriteriaInterface;

/**
 * Class Adjustable
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 *
 * @method void inject(InjectionInterface $injection)
 */
trait Adjustable
{
    /**
     * @var array
     */
    protected $criterias = [];


    /**
     * Bind injection.
     */
    protected function setupAdjustable()
    {
        $this->inject(new ApplyCriteriasBefore());
    }

    /**
     * Save this criteria, it would be auto-applied before every method calling.
     *
     * @param CriteriaInterface $criteria
     */
    public function rememberCriteria(CriteriaInterface $criteria)
    {
        $this->criterias[] = $criteria;
    }

    /**
     * Remove all criterias that this repository remembered.
     */
    public function forgetCriterias()
    {
        $this->criterias = [];
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
     * @param CriteriaInterface $criteria
     */
    public function applyCriteria(CriteriaInterface $criteria)
    {
        /**
         * @var \Housekeeper\Contracts\RepositoryInterface $this
         */
        $criteria->apply($this);
    }

}
<?php namespace Housekeeper\Traits\Repository;

use Housekeeper\Contracts\Injection\BeforeInjectionInterface;
use Housekeeper\Injections\Adjustable\ApplyCriteriasBefore;
use Housekeeper\Contracts\CriteriaInterface;

/**
 * Class Adjustable
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait Adjustable
{
    /**
     * @var array
     */
    protected $criterias = [];


    /**
     * @param CriteriaInterface $criteria
     */
    public function rememberCriteria(CriteriaInterface $criteria)
    {
        $this->criterias[] = $criteria;
    }

    /**
     *
     */
    public function forgetCriterias()
    {
        $this->criterias = [];
    }

    /**
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
        $criteria->apply($this);
    }

    /**
     * Binding injection.
     */
    protected function setupAdjustable()
    {
        $this->inject(new ApplyCriteriasBefore());
    }

}
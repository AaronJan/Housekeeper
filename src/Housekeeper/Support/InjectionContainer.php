<?php

namespace Housekeeper\Support;

use Housekeeper\Contracts\Flow\Before as BeforeFlowContract;
use Housekeeper\Contracts\Flow\After as AfterFlowContract;
use Housekeeper\Contracts\Flow\Reset as ResetFlowContract;
use Housekeeper\Contracts\Injection\Basic as BasicInjectionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectionContract;
use Housekeeper\Contracts\Injection\After as AfterInjectionContract;
use Housekeeper\Contracts\Injection\Reset as ResetInjectionContract;
use Housekeeper\Exceptions\RepositoryException;

/**
 * Class InjectionContainer
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Support
 */
class InjectionContainer
{
    /**
     * @var string
     */
    const GROUP_BEFORE = 'before';

    /**
     * @var string
     */
    const GROUP_AFTER = 'after';

    /**
     * @var string
     */
    const GROUP_RESET = 'reset';

    /**
     * All injections.
     *
     * @var array
     */
    protected $groupedInjections = [
        self::GROUP_BEFORE => [],
        self::GROUP_AFTER  => [],
        self::GROUP_RESET  => [],
    ];

    /**
     * @param      $group
     * @param      $injection
     * @param bool $sort
     */
    protected function addInjection($group, $injection, $sort = true)
    {
        $this->groupedInjections[$group][] = $injection;

        // If need to sort all injections after inject.
        if ($sort) {
            $this->sortInjections($group);
        }
    }

    /**
     * @param \Housekeeper\Contracts\Injection\Before $injection
     * @param bool                                    $sort
     */
    public function addBeforeInjection(BeforeInjectionContract $injection, $sort = true)
    {
        $this->addInjection(static::GROUP_BEFORE, $injection, $sort);
    }

    /**
     * @param \Housekeeper\Contracts\Injection\After $injection
     * @param bool                                   $sort
     */
    public function addAfterInjection(AfterInjectionContract $injection, $sort = true)
    {
        $this->addInjection(static::GROUP_AFTER, $injection, $sort);
    }

    /**
     * @param \Housekeeper\Contracts\Injection\Reset $injection
     * @param bool                                   $sort
     */
    public function addResetInjection(ResetInjectionContract $injection, $sort = true)
    {
        $this->addInjection(static::GROUP_RESET, $injection, $sort);
    }

    /**
     * @param $group
     * @param $flow
     */
    protected function handleFlow($group, $flow)
    {
        foreach ($this->groupedInjections[$group] as $injection) {
            /**
             * @var $injection \Housekeeper\Contracts\Injection\Before|\Housekeeper\Contracts\Injection\After|\Housekeeper\Contracts\Injection\Reset
             */
            $injection->handle($flow);
        }
    }

    /**
     * @param \Housekeeper\Contracts\Flow\Before $flow
     */
    public function handleBeforeFlow(BeforeFlowContract $flow)
    {
        $this->handleFlow(static::GROUP_BEFORE, $flow);
    }

    /**
     * @param \Housekeeper\Contracts\Flow\After $flow
     */
    public function handleAfterFlow(AfterFlowContract $flow)
    {
        $this->handleFlow(static::GROUP_AFTER, $flow);
    }

    /**
     * @param \Housekeeper\Contracts\Flow\Reset $flow
     */
    public function handleResetFlow(ResetFlowContract $flow)
    {
        $this->handleFlow(static::GROUP_RESET, $flow);
    }

    /**
     * Sort by priority ASC.
     *
     * @param string|null $group
     */
    public function sortInjections($group = null)
    {
        if ($group) {
            usort($this->groupedInjections[$group], [$this, 'sortInjection']);
        } else {
            foreach ($this->groupedInjections as $injections) {
                usort($injections, [$this, 'sortInjection']);
            }
        }
    }

    /**
     * Custom function for "usort" used by "sortAllInjections".
     *
     * @param \Housekeeper\Contracts\Injection\Basic $a
     * @param \Housekeeper\Contracts\Injection\Basic $b
     * @return int
     */
    static protected function sortInjection(BasicInjectionContract $a, BasicInjectionContract $b)
    {
        if ($a->priority() == $b->priority()) {
            return 0;
        }

        return ($a->priority() < $b->priority()) ? - 1 : 1;
    }
}
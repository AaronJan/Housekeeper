<?php

namespace Housekeeper\Abilities;

use Housekeeper\Contracts\Action as ActionContract;
use Housekeeper\Action;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class SoftDeletes
 * Allow you to interact with the `SoftDeletes` trait of Eloquent.
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Traits\Repository
 */
trait SoftDeletes
{
    /**
     * Include soft deletes for following use, will triggering a Reset flow.
     *
     * @return $this
     */
    public function startWithTrashed()
    {
        $this->reset(new Action(__METHOD__, [], Action::INTERNAL));

        $this->getCurrentPlan()->startWithTrashed();

        return $this;
    }

    /**
     * Only include soft deletes for following use, will triggering a Reset
     * flow.
     *
     * @return $this
     */
    public function startWithTrashedOnly()
    {
        $this->reset(new Action(__METHOD__, [], Action::INTERNAL));

        $this->getCurrentPlan()->startWithTrashedOnly();

        return $this;
    }


    /**
     * @param $id
     * @return bool|null
     * @throws ModelNotFoundException
     */
    public function forceDelete($id)
    {
        return $this->simpleWrap(Action::DELETE, [$this, '_forceDelete']);
    }

    /**
     * @param $id
     * @return bool|null
     * @throws ModelNotFoundException
     */
    protected function _forceDelete($id)
    {
        $model = $this->getModel()->findOrFail($id);

        $deleted = $model->forceDelete();

        return $deleted;
    }
    
    /**
     * Restore model which is soft-deleted.
     */
    public function restore()
    {
        return $this->simpleWrap(Action::UPDATE, [$this, '_restore']);
    }

    /**
     * @return bool|null
     */
    protected function _restore()
    {
        return $this->getModel()->restore();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
     */
    abstract protected function getModel();

    /**
     * @param               $actionType
     * @param callable|null $function
     * @return mixed
     * @throws \Exception
     */
    abstract protected function simpleWrap($actionType, callable $function = null);

    /**
     * @param \Housekeeper\Contracts\Action $action
     * @return $this
     */
    abstract protected function reset(ActionContract $action);

    /**
     * @return \Housekeeper\Plan
     */
    abstract protected function getCurrentPlan();
}
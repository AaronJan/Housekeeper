<?php

namespace Housekeeper\Abilities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Guardable
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Abilities
 */
trait Guardable
{
    /**
     * @var bool
     */
    protected $guarded = false;

    /**
     * @return $this
     */
    public function guardUp()
    {
        $this->guarded = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function guardDown()
    {
        $this->guarded = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isGuarded()
    {
        return $this->guarded;
    }

    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|static
     */
    protected function _create(array $attributes)
    {
        $model = $this->newModelInstance();

        if ($this->isGuarded()) {
            $model = $model->fill($attributes);
        } else {
            $model = $model->forceFill($attributes);
        }

        $model->save();

        return $model;
    }

    /**
     * @param       $id
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    protected function _update($id, array $attributes)
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $model = $this->getModel()->findOrFail($id);

        if ($this->isGuarded()) {
            $model->fill($attributes);
        } else {
            $model->forceFill($attributes);
        }

        $model->save();

        return $model;
    }

    /**
     * @see \Housekeeper\Repository::getModel
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    abstract protected function getModel();

    /**
     * @see \Housekeeper\Repository::newModelInstance
     *
     * Make a new Model instance.
     *
     * @return Model
     */
    abstract protected function newModelInstance();

    /**
     * @see \Housekeeper\Repository::injectIntoBefore
     *
     * @param \Housekeeper\Contracts\Injection\Before $injection
     * @param bool                                    $sort
     * @return void
     */
    abstract protected function injectIntoBefore(\Housekeeper\Contracts\Injection\Before $injection, $sort = true);
}
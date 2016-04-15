<?php

namespace Housekeeper;

use Housekeeper\Flows\Before as BeforeFlow;
use Housekeeper\Flows\After as AfterFlow;
use Housekeeper\Flows\Reset as ResetFlow;
use Housekeeper\Exceptions\RepositoryException;
use Housekeeper\Contracts\Action as ActionContract;
use Housekeeper\Contracts\Injection\Before as BeforeInjectionContract;
use Housekeeper\Contracts\Injection\After as AfterInjectionContract;
use Housekeeper\Contracts\Injection\Reset as ResetInjectionContract;
use Housekeeper\Contracts\Repository as RepositoryContract;
use Housekeeper\Support\InjectionContainer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class Repository
 *
 * @license        Apache 2.0
 * @copyright  (c) 2016, AaronJan
 * @author         AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package        Housekeeper
 * @version        2.3
 */
abstract class Repository implements RepositoryContract
{
    /**
     * The name of `Boot Method`.
     */
    const BOOT_METHOD = 'boot';

    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $fullModelClassName;

    /**
     * @var int
     */
    protected $planStep;

    /**
     * @var \Housekeeper\Plan[]
     */
    protected $plans;

    /**
     * @var \Housekeeper\Plan
     */
    protected $defaultPlan;

    /**
     * @var InjectionContainer
     */
    protected $injectionContainer;

    /**
     * Page size for pagination.
     *
     * @var int
     */
    protected $perPage;


    /**
     * Specify the full class name of model for this repository.
     *
     * @return string
     */
    abstract protected function model();

    /**
     * Developer could write a `Boot Method` instead `__construct` in child
     * class to make program easier.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->setApp($app);

        $this->initialize();

        $this->callBootable();

        // All official traits for Repository are injecting Injection without
        // sorting for better performance, so when injecting finished, then sort
        // them at once.
        $this->sortAllInjections();

        // Call the `Boot Method` fo the child with Dependency Injection process
        // if that method exists.
        // This provide an easy way to add custom logic that will be executed
        // when repository been created.
        $this->callBoot();

        // Reset to prepare everything that would be used.
        $this->reset(new Action(Action::INTERNAL, [], __METHOD__));
    }

    /**
     *
     */
    protected function callBoot()
    {
        if (method_exists($this, static::BOOT_METHOD)) {
            $this->getApp()->call([$this, static::BOOT_METHOD]);
        }
    }
    
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    protected function setApp(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function getApp()
    {
        return $this->app;
    }
    
    /**
     * @return string
     */
    protected function getKeyName()
    {
        return $this->newModelInstance()->getKeyName();
    }
    
    /**
     * Make a new Model instance.
     *
     * @return Model
     */
    protected function newModelInstance()
    {
        if (is_null($this->fullModelClassName)) {
            $this->fullModelClassName = $this->model();
        }

        return new $this->fullModelClassName;
    }

    /**
     * Read configure from configure file, if it's not exists, "default" will be
     * returned.
     *
     * @param      $key
     * @param null $default
     * @return mixed
     */
    protected function getConfig($key, $default = null)
    {
        $config = $this->getApp()->make('config');

        return $config->get($key, $default);
    }

    /**
     * Validate the model that provided by `model` method, and load configures.
     */
    protected function initialize()
    {
        $this->injectionContainer = new InjectionContainer();

        $model = $this->newModelInstance();

        // The model instance must be an instance of `Model` class from
        // `Laravel`, otherwise just throw an exception.
        if (! $model instanceof Model) {
            throw new RepositoryException(
                "Class \"" . get_class($model) . "\" must be an instance of " . Model::class
            );
        }

        // Load configures from `housekeeper.php` or just use default settings.
        $this->perPage = $this->getConfig('housekeeper.paginate.per_page', 15);
    }

    /**
     * @return \Housekeeper\Support\InjectionContainer
     */
    protected function getInjectionContainer()
    {
        return $this->injectionContainer;
    }

    /**
     * Call all methods that name start with "boot" (must followed by an
     * upper-case latter) with DI process.
     * This allow us to encapsulate injecting logics in trait.
     */
    protected function callBootable()
    {
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getMethods() as $method) {
            $methodName = $method->getName();

            // Method name has to start with "boot" and followed by an
            // upper-case latter.
            if (preg_match('/^boot[A-Z]/', $methodName)) {
                $this->getApp()->call([$this, $methodName]);
            }
        }
    }

    /**
     * Reset the Plan object for the next use.
     */
    protected function resetPlan()
    {
        $this->defaultPlan = new Plan($this->newModelInstance());
        $this->plans       = [];
        $this->planStep    = null;
    }

    /**
     * @return \Housekeeper\Plan
     */
    public function getCurrentPlan()
    {
        return $this->defaultPlan ?: $this->plans[$this->planStep];
    }

    /**
     * @param $offset
     */
    protected function dropPlan($offset)
    {
        unset($this->plans[$offset]);
    }

    /**
     * @return int
     */
    protected function newPlan()
    {
        if ($this->defaultPlan) {
            $offset = $this->planStep = 0;

            $this->plans[$offset] = $this->defaultPlan;
            $this->defaultPlan    = null;
        } else {
            $offset               = ++ $this->planStep;
            $this->plans[$offset] = new Plan($this->newModelInstance());
        }

        return $offset;
    }

    /**
     * @return int
     */
    private function getCurrentWrappedMethodIndex()
    {
        return count($this->plans);
    }

    /**
     * Get model instance from Plan.
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function getModel()
    {
        return $this->getCurrentPlan()->getModel();
    }

    /**
     * @param \Housekeeper\Contracts\Action $action
     * @return $this
     */
    protected function reset(ActionContract $action)
    {
        /**
         * Conditions are use for identify each method call.
         */
        $this->resetPlan();

        /**
         * Make a Reset Flow object.
         */
        $flow = new ResetFlow($this, $action, $this->getCurrentWrappedMethodIndex());

        /**
         * Execute all `Reset` Injections.
         */
        $this->getInjectionContainer()->handleResetFlow($flow);

        return $this;
    }

    /**
     * @param \Housekeeper\Action $action
     * @return \Housekeeper\Flows\Before
     */
    protected function before(Action $action)
    {
        /**
         * Make a Before Flow ojbect.
         */
        $flow = new BeforeFlow($this, $action, $this->getCurrentWrappedMethodIndex());

        /**
         * Execute all `Before` Injections.
         */
        $this->getInjectionContainer()->handleBeforeFlow($flow);

        return $flow;
    }

    /**
     * @param \Housekeeper\Action $action
     * @param                     $returnValue
     * @return \Housekeeper\Flows\After
     */
    protected function after(Action $action, $returnValue)
    {
        // Make a After Flow ojbect.
        $flow = new AfterFlow($this, $action, $this->getCurrentWrappedMethodIndex(), $returnValue);

        // Execute all `After` Injections.
        $this->getInjectionContainer()->handleAfterFlow($flow);

        return $flow;
    }

    /**
     * @param callable           $function
     * @param array              $args
     * @param int|ActionContract $actionType
     * @return mixed
     * @throws \Exception
     */
    protected function wrap(callable $function,
                            array $args,
                            $actionType = ActionContract::UNKNOW)
    {
        // Prepare a Plan object for this wrapped function and returns the
        // offset. This will allow you to call another wrapped internal function
        // that even had queries like "$this->getModel()->where('name', 'kid')"
        // without any affection to each other.
        $planOffset = $this->newPlan();

        // Action indecated this method calling.
        $action = ($actionType instanceof ActionContract) ?
            $actionType :
            new Action(
                $actionType,
                [],
                $this->getMethodNameOfCallable($function),
                $args
            );

        // First it's the Before Flow, if there has any returned in this Flow,
        // then use it as the final returns, jump to the Reset Flow and return
        // the result.
        $beforeFlow = $this->before($action);

        if ($beforeFlow->hasReturnValue()) {
            $this->reset($action);

            return $beforeFlow->getReturnValue();
        }

        // Next, execute the wrapped function and goes to the After Flow.
        try {
            $result = call_user_func_array($function, $action->getArguments());

            // After wrapped function executed, it's the After Flow. In this
            // Flow, injection may alter returns, thus the final returns are
            // come from the After Flow.
            $afterFlow = $this->after($action, $result);

            // Release memory of the Plan, since it will not be used anymore.
            $this->dropPlan($planOffset);

            return $afterFlow->getReturnValue();
        } catch (\Exception $e) {
            // Bubble up the exception.
            throw $e;
        } finally {
            // No matter what happens, must go to the Reset Flow.
            $this->reset($action);
        }
    }

    /**
     * @param callable $function
     * @return string
     */
    private function getMethodNameOfCallable(callable $function)
    {
        return ($function instanceof \Closure) ?
            '\\Closure' :
            $function[1];
    }
    
    /**
     * @param int|ActionContract $actionType
     * @param callable|null      $function
     * @return mixed
     * @throws \Exception
     */
    protected function simpleWrap($actionType, callable $function = null)
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];

        return $this->wrap(
            ($function ?: $this->traceToRealMethod($caller['function'])),
            $caller['args'],
            $actionType
        );
    }

    /**
     * Get the real method that will be executed.
     *
     * By convention, the real method should be named start with an underscore
     * follow by the cover method's name, for instance: cover method named
     * "getUserName", so the real method should be "_getUserName".
     *
     * @param null|string $coverMethodName
     * @return Callable|array
     */
    protected function traceToRealMethod($coverMethodName = null)
    {
        if (! $coverMethodName) {
            $coverMethodName = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        }

        return [$this, "_{$coverMethodName}"];
    }

    /**
     * @param \Housekeeper\Contracts\Injection\Before $injection
     * @param bool                                    $sort
     */
    protected function injectIntoBefore(BeforeInjectionContract $injection, $sort = true)
    {
        $this->getInjectionContainer()->addBeforeInjection($injection, $sort);
    }

    /**
     * @param \Housekeeper\Contracts\Injection\After $injection
     * @param bool                                   $sort
     */
    protected function injectIntoAfter(AfterInjectionContract $injection, $sort = true)
    {
        $this->getInjectionContainer()->addAfterInjection($injection, $sort);
    }

    /**
     * @param \Housekeeper\Contracts\Injection\Reset $injection
     * @param bool                                   $sort
     */
    protected function injectIntoReset(ResetInjectionContract $injection, $sort = true)
    {
        $this->getInjectionContainer()->addResetInjection($injection, $sort);
    }

    /**
     * Sort all event handlers by priority ASC.
     */
    protected function sortAllInjections()
    {
        $this->getInjectionContainer()->sortInjections();
    }

    /**
     * This is more semantic than `applyWheres`.
     *
     * @param array $wheres
     * @return $this
     */
    public function whereAre(array $wheres)
    {
        $this->getCurrentPlan()->applyWheres($wheres);

        return $this;
    }

    /**
     * @param array $wheres
     * @return $this
     */
    public function applyWheres(array $wheres)
    {
        return $this->whereAre($wheres);
    }

    /**
     * @param        $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->getCurrentPlan()->applyOrderBy($column, $direction);

        return $this;
    }

    /**
     * Same as the `orderBy`.
     *
     * @param        $column
     * @param string $direction
     * @return $this
     */
    public function applyOrderBy($column, $direction = 'asc')
    {
        return $this->orderBy($column, $direction);
    }

    /**
     * Set the relationships that should be eager loaded, just like Eloquent.
     *
     * @return $this
     */
    public function with()
    {
        call_user_func_array([$this->getCurrentPlan(), 'applyWith'], func_get_args());

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function offset($value)
    {
        $this->getCurrentPlan()->applyOffset($value);

        return $this;
    }
    
    /**
     * @param $value
     * @return $this
     */
    public function limit($value)
    {
        $this->getCurrentPlan()->applyLimit($value);

        return $this;
    }

    /**
     * @param      $id
     * @param null $primaryKeyName
     * @return mixed
     * @throws \Exception
     */
    public function exists($id, $primaryKeyName = null)
    {
        return $this->simpleWrap(Action::READ, [$this, '_exists']);
    }

    /**
     * @param      $id
     * @param null $primaryKeyName
     * @return bool
     */
    protected function _exists($id, $primaryKeyName = null)
    {
        /**
         * Fix auto-completion.
         *
         * @var \Illuminate\Database\Query\Builder $model
         */
        $model = $this->getModel();

        $primaryKeyName = ($primaryKeyName ?: $model->getKeyName());

        return $model->where($primaryKeyName, $id)->exists();
    }
    
    /**
     * @param string $columns
     * @return int
     */
    public function count($columns = '*')
    {
        return $this->simpleWrap(Action::READ, [$this, '_count']);
    }

    /**
     * @param string $columns
     * @return int
     */
    protected function _count($columns = '*')
    {
        return $this->getModel()->count($columns);
    }

    /**
     * @param       $id
     * @param array $columns
     * @return Model
     * @throws \Exception
     */
    public function find($id, $columns = ['*'])
    {
        return $this->simpleWrap(Action::READ, [$this, '_find']);
    }

    /**
     * Find data by id
     *
     * @param       $id
     * @param array $columns
     * @return Model
     */
    protected function _find($id, $columns = ['*'])
    {
        return $this->getModel()->findOrFail($id, $columns);
    }

    /**
     * Same as the "findMany" method of Eloquent.
     *
     * @param array $ids
     * @param array $columns
     * @return EloquentCollection
     */
    public function findMany($ids, $columns = ['*'])
    {
        return $this->simpleWrap(Action::READ, [$this, '_findMany']);
    }

    /**
     * @param array $ids
     * @param array $columns
     * @return EloquentCollection
     */
    protected function _findMany($ids, $columns = ['*'])
    {
        return $this->getModel()->findMany($ids, $columns);
    }

    /**
     * Save a new model and return the instance, like the same method of
     * Eloquent.
     *
     * @param array $attributes
     * @return Model
     * @throws \Exception
     */
    public function create(array $attributes)
    {
        return $this->simpleWrap(Action::CREATE, [$this, '_create']);
    }

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return Model
     */
    protected function _create(array $attributes)
    {
        $model = $this->newModelInstance()->forceFill($attributes);

        $model->save();

        return $model;
    }

    /**
     * @param $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function delete($id)
    {
        return $this->simpleWrap(Action::DELETE, [$this, '_delete']);
    }

    /**
     * @param $id
     * @return bool|null
     * @throws ModelNotFoundException
     */
    protected function _delete($id)
    {
        $model = $this->getModel()->findOrFail($id);

        $deleted = $model->delete();

        return $deleted;
    }

    /**
     * Update a record in the database, could use a model as $id.
     *
     * @param mixed $id
     * @param array $attributes
     * @return Model
     */
    public function update($id, array $attributes)
    {
        return $this->simpleWrap(Action::UPDATE, [$this, '_update']);
    }

    /**
     * @param mixed $id
     * @param array $attributes
     * @return Model
     */
    protected function _update($id, array $attributes)
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $model = $this->getModel()
            ->findOrFail($id)
            ->forceFill($attributes);

        $model->save();

        return $model;
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return EloquentCollection
     */
    public function all($columns = ['*'])
    {
        return $this->simpleWrap(Action::READ, [$this, '_all']);
    }

    /**
     * @param array $columns
     * @return EloquentCollection
     */
    protected function _all($columns = ['*'])
    {
        return $this->getModel()->get($columns);
    }

    /**
     * Same as the "paginate" method of Eloquent.
     *
     * @param int|null $limit
     * @param array    $columns
     * @return LengthAwarePaginator
     */
    public function paginate($limit = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        return $this->simpleWrap(Action::READ, [$this, '_paginate']);
    }

    /**
     * @param int|null $limit
     * @param array    $columns
     * @return LengthAwarePaginator
     */
    protected function _paginate($limit = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $limit = $limit ?: $this->perPage;

        return $this->getModel()->paginate($limit, $columns, $pageName, $page);
    }

    /**
     * Get models by a simple equality query.
     *
     * @param       $field
     * @param null  $value
     * @param array $columns
     * @return array|EloquentCollection
     */
    public function getByField($field, $value = null, $columns = ['*'])
    {
        return $this->simpleWrap(Action::READ, [$this, '_getByField']);
    }

    /**
     * @param       $field
     * @param null  $value
     * @param array $columns
     * @return array|EloquentCollection
     */
    protected function _getByField($field, $value = null, $columns = ['*'])
    {
        return $this->getModel()->where($field, '=', $value)->get($columns);
    }

    /**
     * @deprecated This is not a frequently used method.
     *
     * Get one model by a simple equality query.
     *
     * @param       $field
     * @param null  $value
     * @param array $columns
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByField($field, $value = null, $columns = ['*'])
    {
        return $this->simpleWrap(Action::READ, [$this, '_findByField']);
    }

    /**
     * @param       $field
     * @param null  $value
     * @param array $columns
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function _findByField($field, $value = null, $columns = ['*'])
    {
        return $this->getModel()->where($field, '=', $value)->firstOrFail($columns);
    }
}

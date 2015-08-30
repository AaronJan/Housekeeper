<?php

namespace Housekeeper\Eloquent;

use Housekeeper\Action;
use Housekeeper\Contracts\Injection\AfterInjectionInterface;
use Housekeeper\Contracts\Injection\BeforeInjectionInterface;
use Housekeeper\Contracts\Injection\InjectionInterface;
use Housekeeper\Contracts\Injection\ResetInjectionInterface;
use Housekeeper\Contracts\RepositoryInterface;
use Housekeeper\Exceptions\RepositoryException;
use Housekeeper\Flows\After;
use Housekeeper\Flows\Before;
use Housekeeper\Flows\Reset;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class BaseRepository
 *
 * @author  AaronJan <https://github.com/AaronJan/Housekeeper>
 * @package Housekeeper\Eloquent
 */
abstract class BaseRepository implements RepositoryInterface
{

    /**
     * @var Application
     */
    protected $app;

    /**
     * Model instance.
     *
     * @var Model|Builder|EloquentBuilder
     */
    protected $model;

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * All method injections.
     *
     * @var array
     */
    protected $injections = [
        'reset'  => [],
        'before' => [],
        'after'  => []
    ];

    /**
     * Page size for pagination.
     *
     * @var int
     */
    protected $perPage;


    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract protected function model();

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->loadConfig();
        $this->setup();
        $this->reset(new Action(__METHOD__, []));
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
        $config = $this->app->make('config');

        return $config->get($key, $default);
    }

    /**
     * Load configures from configure file.
     */
    protected function loadConfig()
    {
        $this->perPage = $this->getConfig('housekeeper.repository.paginate.perPage', 15);
    }

    /**
     * Call all setup methods that name start with "setup", like: "setupCache".
     * So you can write function to binding event listener.
     */
    protected function setup()
    {
        $reflection = new \ReflectionClass($this);

        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $methodName = $method->getName();
            if (preg_match('/^setup[A-Z]/', $methodName)) {
                call_user_func([$this, $methodName]);
            }
        }
    }

    /**
     * @throws RepositoryException
     */
    protected function freshModel()
    {
        $model = $this->modelInstance();

        if ( ! $model instanceof Model) throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        $this->model = $model;
    }

    /**
     * Make a new Model instanse.
     *
     * @return mixed
     * @throws RepositoryException
     */
    protected function modelInstance()
    {
        $modelName = '\\' . ltrim($this->model(), '\\');

        if ($modelName == '') throw new RepositoryException("You should return the name of Model in \"Model\".");

        return new $modelName;
    }

    /**
     * Wrap function with event implements.
     *
     * @param callable $func
     * @param Action   $action
     * @return mixed
     */
    protected function wrap(callable $func, Action $action)
    {
        /**
         * First it's Before Flow.
         */
        $beforeFlow = $this->before($action);
        if ($beforeFlow->hasReturn()) return $beforeFlow->getReturn();

        /**
         * Than execute core function.
         */
        try {
            $result = call_user_func_array($func, $action->getArguments());

            /**
             * After core function executed, it's After Flow.
             */
            $afterFlow = $this->after($action, $result);

            return $afterFlow->getReturn();
        } finally {
            /**
             * In the end, call Reset Flow.
             */
            $this->reset($action);
        }

        return null;
    }

    /**
     * @param Action $action
     * @throws RepositoryException
     */
    public function reset(Action $action)
    {
        /**
         * Need to make a fresh model every time.
         */
        $this->freshModel();

        /**
         * Conditions are use for identify each method call.
         */
        $this->resetConditons();

        /**
         * Make a Reset Flow object.
         */
        $flow = new Reset($this, $action);

        /**
         * Execute all Reset Injections.
         *
         * @var \Housekeeper\Contracts\Injection\ResetInjectionInterface $injection
         */
        foreach ($this->injections['reset'] as $injection) {
            $injection->handle($flow);
        }
    }

    /**
     * @param Action $action
     * @return Before
     */
    protected function before(Action $action)
    {
        /**
         * Make a Before Flow ojbect.
         */
        $flow = new Before($this, $action);

        /**
         * @var \Housekeeper\Contracts\Injection\BeforeInjectionInterface $injection
         */
        foreach ($this->injections['before'] as $injection) {
            $injection->handle($flow);
        }

        return $flow;
    }

    /**
     * @param Action $action
     * @param        $returnValue
     * @return After
     */
    protected function after(Action $action, $returnValue)
    {
        /**
         * Make a After Flow ojbect.
         */
        $flow = new After($this, $action, $returnValue);

        /**
         * @var \Housekeeper\Contracts\Injection\AfterInjectionInterface $injection
         */
        foreach ($this->injections['after'] as $injection) {
            $injection->handle($flow);
        }

        return $flow;
    }

    /**
     * @param  array      $where
     * @param  bool|false $or
     * @return $this
     */
    public function applyWhere(array $where, $or = false)
    {
        /**
         * Determine using "where" or "orWhere".
         */
        $whereFunction = $or ? 'orWhere' : 'where';

        /**
         * Save to conditons.
         */
        $this->addCondition($whereFunction, $where);

        foreach ($where as $field => $value) {
            if ($value instanceof \Closure) {
                /**
                 * Use Closure for complex query search.
                 */
                $this->model = $this->model->$whereFunction($value);
            } elseif (is_array($value) && count($value) == 3) {
                /**
                 * If $value has 3 variables, then just ignore the $field, use
                 * what's in the $value instead.
                 */
                list($field, $condition, $val) = $value;
                $this->model = $this->model->$whereFunction($field, $condition, $val);
            } else {
                /**
                 * Simple equality compare.
                 */
                $this->model = $this->model->$whereFunction($field, '=', $value);
            }
        }

        return $this;
    }

    /**
     * @param        $column
     * @param string $direction
     * @return $this
     */
    public function applyOrder($column, $direction = 'asc')
    {
        /**
         * Save to conditons.
         */
        $this->addCondition('order', [$column, $direction]);

        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Every single action that would afftected returns, should add a condition.
     *
     * @param string $type
     * @param mixed  $condition
     */
    protected function addCondition($type, $condition)
    {
        $this->conditions[] = [
            $type => $condition
        ];
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Conditions are use for identify each method call.
     */
    protected function resetConditons()
    {
        $this->conditions = [];
    }

    /**
     * Inject a Flow Injection to adding custom logic.
     *
     * @param InjectionInterface $injection
     * @throws RepositoryException
     */
    protected function inject(InjectionInterface $injection)
    {
        /**
         * Add injection.
         */
        if ($injection instanceof ResetInjectionInterface) {
            $this->injections['reset'][] = $injection;
        } elseif ($injection instanceof BeforeInjectionInterface) {
            $this->injections['before'][] = $injection;
        } elseif ($injection instanceof AfterInjectionInterface) {
            $this->injections['after'][] = $injection;
        } else {
            throw new RepositoryException('Unusable Injection.');
        }

        /**
         * Sort injections.
         */
        $this->sortAllInjections();
    }

    /**
     * Sort all event handlers by priority ASC.
     */
    protected function sortAllInjections()
    {
        foreach ($this->injections as &$handlers) {
            usort($handlers, [$this, 'sortInjection']);
        }
    }

    /**
     * @param InjectionInterface $a
     * @param InjectionInterface $b
     * @return int
     */
    static protected function sortInjection(InjectionInterface $a, InjectionInterface $b)
    {
        if ($a->priority() == $b->priority()) {
            return 0;
        }

        return ($a->priority() < $b->priority()) ? -1 : 1;
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return mixed|Collection
     */
    public function all($columns = ['*'])
    {
        return $this->wrap(function ($columns = ['*']) {

            return $this->model->get($columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * Retrieve all data of repository, paginated
     *
     * @param null  $limit
     * @param array $columns
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*'])
    {
        return $this->wrap(function ($limit = null, $columns = ['*']) {

            $limit = is_null($limit) ? $this->perPage : $limit;

            return $this->model->paginate($limit, $columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     * @return mixed|Model
     */
    public function findByField($field, $value = null, $columns = ['*'])
    {
        return $this->wrap(function ($field, $value = null, $columns = ['*']) {

            return $this->model->where($field, '=', $value)->get($columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * Find data by multiple fields
     *
     * @param array $where
     * @param array $columns
     * @return mixed|Model
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        return $this->wrap(function ($where, $columns = ['*']) {

            $this->applyWhere($where);

            return $this->model->get($columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }


    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return mixed|Model
     */
    public function create(array $attributes)
    {
        return $this->wrap(function ($attributes) {

            $model = $this->model->newInstance($attributes);
            /**
             * @var Model $model
             */
            $model->save();

            return $model;

        }, new Action(__METHOD__, func_get_args(), Action::CREATE));
    }

    /**
     * Update a entity in repository by id
     *
     * @param       $id
     * @param array $attributes
     * @return mixed|Model
     */
    public function update($id, array $attributes)
    {
        return $this->wrap(function ($id, array $attributes) {

            /**
             * @var Model $model
             */
            $model = $this->model->findOrFail($id);
            $model->fill($attributes);
            $model->save();

            return $model;

        }, new Action(__METHOD__, func_get_args(), Action::UPDATE));
    }

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     * @return int
     */
    public function delete($id)
    {
        return $this->wrap(function ($id) {

            $model = $this->model->findOrFail($id);

            $deleted = $model->delete();

            return $deleted;

        }, new Action(__METHOD__, func_get_args(), Action::DELETE));
    }

    /**
     * Find data by id
     *
     * @param       $id
     * @param array $columns
     * @return mixed|Model
     */
    public function find($id, $columns = ['*'])
    {
        return $this->wrap(function ($id, $columns = ['*']) {

            return $this->model->findOrFail($id, $columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * Load relations
     *
     * @param array|string $relations
     * @return $this
     */
    public function with($relations)
    {
        /**
         * Save to conditons.
         */
        $this->addCondition('with', $relations);

        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Reset and includes soft deletes for following queries.
     *
     * @return $this
     */
    public function startWithTrashed()
    {
        $this->reset(new Action(__METHOD__, []));

        /**
         * Save to conditons.
         */
        $this->addCondition('withTrashed', 'withTrashed');

        $this->model = $this->model->withTrashed();

        return $this;
    }

    /**
     * Reset and only includes soft deletes for following queries.
     *
     * @return $this
     */
    public function startWithTrashedOnly()
    {
        $this->reset(new Action(__METHOD__, []));

        /**
         * Save to conditons.
         */
        $this->addCondition('onlyTrashed', 'onlyTrashed');

        $this->model = $this->model->onlyTrashed();

        return $this;
    }

}

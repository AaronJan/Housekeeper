<?php namespace Housekeeper\Eloquent;

use Housekeeper\Contracts\Injection\InjectionInterface;
use Housekeeper\Contracts\Injection\ResetInjectionInterface;
use Housekeeper\Contracts\Injection\BeforeInjectionInterface;
use Housekeeper\Contracts\Injection\AfterInjectionInterface;
use Housekeeper\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Housekeeper\Exceptions\RepositoryException;
use Housekeeper\Flow\Reset;
use Housekeeper\Flow\Before;
use Housekeeper\Flow\After;
use Illuminate\Contracts\Foundation\Application;
use Housekeeper\Action;

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
     * @var Model|Builder
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
    protected $perPage = 15;


    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract protected function model();

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

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
     * Load configures from config file.
     */
    protected function loadConfig()
    {
        $config = $this->app->make('config');

        /**
         * How many entries per page.
         */
        $this->perPage = $config->get('housekeeper.repository.paginate.perPage', 15);
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
                call_user_func(array($this, $methodName));
            }
        }
    }

    /**
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
            usort($handlers, array($this, 'sortInjection'));
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
     * Conditions are use for identify each method call.
     */
    protected function resetConditons()
    {
        $this->conditions = [];
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
     * @return Model|Builder
     */
    protected function modelInstance()
    {
        $modelName = '\\' . ltrim($this->model(), '\\');

        return new $modelName;
    }

    /**
     * @param string $type
     * @param mixed  $condition
     */
    protected function addConditon($type, $condition)
    {
        $this->conditions[] = [
            $type => $condition
        ];
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
        $result = call_user_func_array($func, $action->getArguments());

        /**
         * After core function executed, it's After Flow.
         */
        $afterFlow = $this->after($action, $result);

        /**
         * In the end, call Reset Flow.
         */
        $this->reset($action);

        return $afterFlow->getReturn();
    }

    /**
     * @param array $where
     */
    public function applyWhere(array $where)
    {
        /**
         * Save to conditons.
         */
        $this->addConditon('where', $where);

        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                $this->model = $this->model->where($field, $condition, $val);
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     * @return mixed
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
    public function paginate($limit = null, $columns = array('*'))
    {
        return $this->wrap(function ($limit, $columns) {

            $limit = is_null($limit) ? $this->perPage : $limit;

            return $this->model->paginate($limit, $columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * Find data by id
     *
     * @param       $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->wrap(function ($id, $columns) {

            return $this->model->findOrFail($id, $columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     * @return mixed
     */
    public function findByField($field, $value = null, $columns = array('*'))
    {
        return $this->wrap(function ($field, $value, $columns) {

            return $this->model->where($field, '=', $value)->get($columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * Find data by multiple fields
     *
     * @param array $where
     * @param array $columns
     * @return mixed
     */
    public function findWhere(array $where, $columns = array('*'))
    {
        return $this->wrap(function ($where, $columns) {

            $this->applyWhere($where);

            return $this->model->get($columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return mixed
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
     * @return mixed
     */
    public function update($id, array $attributes)
    {
        return $this->wrap(function ($id, $attributes) {

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
        return $this->wrap(function ($id, $attributes) {

            $model = $this->find($id);

            $deleted = $model->delete();

            return $deleted;

        }, new Action(__METHOD__, func_get_args(), Action::DELETE));
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
        $this->addConditon('with', $relations);

        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Set hidden fields
     *
     * @param array $fields
     * @return $this
     */
    public function hidden(array $fields)
    {
        $this->model->setHidden($fields);

        return $this;
    }

    /**
     * Set visible fields
     *
     * @param array $fields
     * @return $this
     */
    public function visible(array $fields)
    {
        $this->model->setVisible($fields);

        return $this;
    }

}

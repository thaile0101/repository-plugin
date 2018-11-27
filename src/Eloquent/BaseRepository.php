<?php
/**
 * Created by PhpStorm.
 * User: vulh
 * Date: 2/16/17
 * Time: 6:07 PM
 */

namespace Elidev\Repository\Eloquent;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Elidev\Repository\Contracts\RepositoryInterface;
use Elidev\Repository\Contracts\CriteriaInterface;
use Elidev\Repository\Contracts\RepositoryCriteriaInterface;
use Illuminate\Container\Container as Application;

abstract class BaseRepository implements RepositoryInterface, RepositoryCriteriaInterface
{

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Model
     */
    protected $model;

    /**
     * Collection of Criteria
     *
     * @var Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;


    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->criteria = new Collection();
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model();

    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        $this->applyCriteria();

        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns);
        } else {
            $results = $this->model->all($columns);
        }
        $this->resetModel();

        return $results;
    }

    /**
     * Retrieve all data of repository, paginated
     *
     * @param null   $limit
     * @param array  $columns
     * @param string $method
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*'], $method = "paginate")
    {
        $this->applyCriteria();
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $results = $this->model->{$method}($limit, $columns);
        $results->appends(app('request')->query());
        $this->resetModel();

        return $results;
    }

    public function getLimitOffset($limit = null, $offset, $columns = ['*'], $page = 1){
        $this->applyCriteria();
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        return $this->model->offset($offset + ($page - 1) * $limit)->limit($limit)->get($columns);
    }

    /**
     * Retrieve all data of repository, simple paginated
     *
     * @param null  $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function simplePaginate($limit = null, $columns = ['*'])
    {
        return $this->paginate($limit, $columns, "simplePaginate");
    }

    /**
     * Find data by id
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $model = $this->model->find($id, $columns);
        $this->resetModel();

        return $model;
    }

    /**
     * Find soft deleted data by id
     *
     * @param integer $id
     * @param array $columns
     *
     * @return Object Model
     */
    public function findSoftDelete($id, $columns = ['*'])
    {
        return $this->model->select($columns)->onlyTrashed()->where('id', $id)->first();
    }

    /**
     * Find data by id
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $model = $this->model->findOrFail($id, $columns);
        $this->resetModel();

        return $model;
    }

    /**
     * Find data by field and value
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findByField($field, $value = null, $columns = ['*'])
    {
        $this->applyCriteria();
        $model = $this->model->where($field, '=', $value)->get($columns);
        $this->resetModel();

        return $model;
    }

    /**
     * Find data by multiple fields
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyConditions($where);

        $model = $this->model->get($columns);
        $this->resetModel();

        return $model;
    }

    /**
     * Count where
     *
     * @param array $where
     *
     * @return integer
     */
    public function countWhere(array $where)
    {
        $this->applyCriteria();
        $this->applyConditions($where);

        $count = $this->model->count();
        $this->resetModel();

        return $count;
    }

    /**
     * Find wheres model
     *
     * @param array $where
     * @return Models
     */
    public function findWhereModels(array $where)
    {
        $this->applyCriteria();
        $this->applyConditions($where);

        $model = $this->model;
        $this->resetModel();

        return $model;
    }

    /**
     * Find data by multiple values in one field
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        $this->applyCriteria();
        $model = $this->model->whereIn($field, $values)->get($columns);
        $this->resetModel();

        return $model;
    }

    /**
     * Find data by excluding multiple values in one field
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereNotIn($field, array $values, $columns = ['*'])
    {
        $this->applyCriteria();
        $model = $this->model->whereNotIn($field, $values)->get($columns);
        $this->resetModel();

        return $model;
    }

    /**
     * Order collection by a given column
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Order collection by a given query
     *
     * @param string $query
     * @param string $direction
     *
     * @return $this
     */
    public function orderByRaw($query, $direction = 'asc')
    {
        $this->model = $this->model->orderByRaw($query . " ".$direction);

        return $this;
    }

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes)
    {
        $model = $this->model->newInstance($attributes);
        $model->save();

        return $model;
    }

    /**
     * Save new entities in repository
     *
     * @param array $items
     *
     * @return mixed
     */
    public function createMultiples(array $items)
    {
        return $this->model()::insert($items);
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param       $id
     *create
     * @return mixed
     */
    public function update(array $attributes, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id)
    {
        $model = $this->findOrFail($id);

        $deleted = $model->delete();

        return $deleted;
    }

    /**
     * Applies the given where conditions to the model.
     *
     * @param array $where
     * @return void
     */
    protected function applyConditions(array $where)
    {
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
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Apply criteria in current Query
     *
     * @return $this
     */
    protected function applyCriteria()
    {

        if ($this->skipCriteria === true) {
            return $this;
        }

        $criteria = $this->getCriteria();
        $model = $this->model;

        if ($criteria) {
            foreach ($criteria as $c) {
                if (is_array($c)) {
                    $this->model = $model->where(function($model) use ($c) {
                        foreach ($c as $c1) {
                            $c1->apply($model, $this);
                        }
                        return $model;
                    });
                    continue;
                }

                if ($c instanceof CriteriaInterface) {
                    $this->model = $c->apply($this->model, $this);
                }
            }
        }

        return $this;
    }


    /**
     * Push Criteria for filter the query
     *
     * @param $criteria
     *
     * @return $this
     */
    public function pushCriteria($criteria)
    {
        if (is_string($criteria)) {
            $criteria = new $criteria;
        }
        if (!$criteria instanceof CriteriaInterface) {
            //throw new RepositoryException("Class " . get_class($criteria) . " must be an instance of Prettus\\Repository\\Contracts\\CriteriaInterface");
        }

        $this->criteria->push($criteria);

        return $this;
    }

    /**
     * Pop Criteria
     *
     * @param $criteria
     *
     * @return $this
     */
    public function popCriteria($criteria)
    {
        $this->criteria = $this->criteria->reject(function ($item) use ($criteria) {
            if (is_object($item) && is_string($criteria)) {
                return get_class($item) === $criteria;
            }

            if (is_string($item) && is_object($criteria)) {
                return $item === get_class($criteria);
            }

            return get_class($item) === get_class($criteria);
        });

        return $this;
    }

    /**
     * Get Collection of Criteria
     *
     * @return Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Find data by Criteria
     *
     * @param CriteriaInterface $criteria
     *
     * @return mixed
     */
    public function getByCriteria(CriteriaInterface $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);
        $results = $this->model->get();
        $this->resetModel();

        return $this->parserResult($results);
    }

    /**
     * Skip Criteria
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;

        return $this;
    }

    /**
     * Reset all Criterias
     *
     * @return $this
     */
    public function resetCriteria()
    {
        $this->criteria = new Collection();

        return $this;
    }

    public function resetModel()
    {
        $this->makeModel();
    }

    /**
     * Init instance
     * @param array $attributes
     * @return model
     */
    public function newInstance(array $attributes = []) {
        return $this->model->newInstance($attributes);
    }
}
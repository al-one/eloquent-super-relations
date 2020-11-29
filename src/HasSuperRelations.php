<?php

namespace Alone\EloquentSuperRelations;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\Relation;

trait HasSuperRelations
{

    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }

    /**
     * Get a relationship value from a method.
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromMethod($method)
    {
        /**
         * @var  $relation  Relation
         */
        $relation = $this->$method();
        $results  = $this->eagerLoadRelationFromModel($relation, $method, [$this]);
        if(is_null($results)) {
            $results = parent::getRelationshipFromMethod($method);
        }

        return $results;
    }

    /**
     * Get a relationship value from parent model.
     *
     * @param  Relation    $relation
     * @param  string      $name  relationship name
     * @param  array|null  $models
     *
     * @return mixed
     */
    public function eagerLoadRelationFromModel(Relation $relation, $name, array $models = null)
    {
        $results = null;
        $parent = $relation->getParent();
        $method = 'eagerLoad'.Str::studly($name);

        if(method_exists($parent, $method)) {
            $query = $relation->getQuery()->getQuery();
            $where = array_reduce($query->wheres ?: [], function($dat, $v) use ($query) {
                if(isset($v['values']) || isset($v['value'])) {
                    $key = Arr::get($v, 'column');
                    if(Str::startsWith($key, "{$query->from}.")) {
                        $key = substr($key, strlen($query->from) + 1);
                    }
                    $dat[$key] = $v['values'] ?? [$v['value']];
                }

                return $dat;
            },[]);
            $results = $parent->$method($relation, isset($models) ? $models : [$this], $where);
        }

        return $results;
    }

}

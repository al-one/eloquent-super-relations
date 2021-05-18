<?php

namespace Alone\EloquentSuperRelations;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Collection;

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
         * @var  $relation  Relations\Relation
         */
        $relation = $this->$method();
        $results  = $this->eagerLoadRelationFromModel($relation, $method, [$this]);
        if(is_null($results)) {
            $results = parent::getRelationshipFromMethod($method);
        } else {
            if($relation instanceof Relations\HasOne
            || $relation instanceof Relations\MorphOne
            || $relation instanceof Relations\BelongsTo
            ) {
                if($results instanceof Collection) {
                    $results = $results->first();
                }
            }
            $this->setRelation($method, $results);
        }

        return $results;
    }

    /**
     * Get a relationship value from parent model.
     *
     * @param  Relations\Relation  $relation
     * @param  string      $name  relationship name
     * @param  array|null  $models
     *
     * @return mixed
     */
    public function eagerLoadRelationFromModel(Relations\Relation $relation, $name, array $models = null)
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
            if(is_array($results)) {
                if(is_array(Arr::first($results))) {
                    $results = $parent->hydrate($results);
                } else {
                    $results = $parent->newFromBuilder($results);
                }
            }
            if($results instanceof Model) {
                $results = $parent->newCollection([$results]);
            }
            if($results instanceof Collection) {
                if($with = $relation->getQuery()->getEagerLoads()) {
                    $results->load($with);
                }
            }
        }

        return $results;
    }

}

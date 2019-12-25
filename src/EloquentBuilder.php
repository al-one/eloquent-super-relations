<?php

namespace Alone\EloquentSuperRelations;

use Illuminate\Database\Eloquent\Builder;

class EloquentBuilder extends Builder
{

    /**
     * Eagerly load the relationship on a set of models.
     *
     * @param  array     $models
     * @param  string    $name  relationship name
     * @param  \Closure  $constraints
     * @return array
     */
    protected function eagerLoadRelation(array $models,$name,\Closure $constraints)
    {
        $relation = $this->getRelation($name);
        $relation->addEagerConstraints($models);
        $constraints($relation);
        $models = $relation->initRelation($models,$name);
        $parent = $relation->getParent();

        $results = null;
        if(method_exists($parent,'eagerLoadRelationFromModel'))
        {
            $results = $parent->eagerLoadRelationFromModel($relation,$name,$models);
        }
        if(is_null($results))
        {
            $results = $relation->getEager();
        }

        return $relation->match($models,$results,$name);
    }

}

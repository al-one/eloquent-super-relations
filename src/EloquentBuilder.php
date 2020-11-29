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
     *
     * @return array
     */
    protected function eagerLoadRelation(array $models, $name, \Closure $constraints)
    {
        // First we will "back up" the existing where conditions on the query so we can
        // add our eager constraints. Then we will merge the wheres that were on the
        // query back to it in order that any where conditions might be specified.
        $relation = $this->getRelation($name);
        $relation->addEagerConstraints($models);

        $constraints($relation);

        $models = $relation->initRelation($models, $name);
        $parent = $relation->getParent();

        $results = null;
        if(method_exists($parent, 'eagerLoadRelationFromModel')) {
            $results = $parent->eagerLoadRelationFromModel($relation, $name, $models);
        }
        if(is_null($results)) {
            $results = $relation->getEager();
        }

        // Once we have the results, we just match those back up to their parent models
        // using the relationship instance. Then we just return the finished arrays
        // of models which have been eagerly hydrated and are readied for return.
        return $relation->match($models, $results, $name);
    }

}

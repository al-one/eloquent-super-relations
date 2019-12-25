<?php

namespace Alone\EloquentSuperRelations;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations;

trait HasSuperRelations
{

    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }

    protected function getRelationshipFromMethod($method)
    {
        /**
         * @var $relation Relations\Relation
         */
        $relation = $this->$method();
        $results  = $this->eagerLoadRelationFromModel($relation,$method,[$this]);
        if(is_null($results))
        {
            $results = parent::getRelationshipFromMethod($method);
        }
        return $results;
    }

    public function eagerLoadRelationFromModel(Relations\Relation $relation,$name,array $models = null)
    {
        $results = null;
        $parent = $relation->getParent();
        $fun = 'eagerLoad'.Str::studly($name);
        if(method_exists($parent,$fun))
        {
            $query = $relation->getQuery()->getQuery();
            $from  = data_get($query,'from');
            $where = collect(data_get($query,'wheres',[]))->reduce(function($dat,$v) use($from)
            {
                if(isset($v['values']) || isset($v['value']))
                {
                    $key = data_get($v,'column');
                    if(Str::startsWith($key,"$from."))
                    {
                        $key = substr($key,strlen($from) + 1);
                    }
                    $dat[$key] = data_get($v,'values',[data_get($v,'value')]);
                }
                return $dat;
            },[]);
            $results = $parent->$fun($relation,isset($models) ? $models : [$this],$where);
        }
        return $results;
    }

}

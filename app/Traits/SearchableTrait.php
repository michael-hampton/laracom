<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait SearchableTrait {

    protected static function applyDecoratorsFromRequest(Request $request, Builder $query) {

        foreach ($request->all() as $filterName => $value) {

            if (in_array($filterName, ['_token', '_method', 'module'])) {
                continue;
            }

            
            if(empty($value) && (int)$value !== 0 || $value === null) {
                continue;
            }


            $decorator = static::createFilterDecorator($filterName);

            if (static::isValidDecorator($decorator)) {
                
                $query = $decorator::apply($query, $value);
            }
        }
                
        return $query;
    }

    /**
     * 
     * @param string $name
     * @return string
     */
    protected static function createFilterDecorator(string $name) {


        $class = 'App\Search\\Filters\\' .
                ucfirst
                        (str_replace(' ', '', ucwords(str_replace('_', ' ', $name))
                        )
        );

        return $class;
    }

    protected static function isValidDecorator($decorator) {
        return class_exists($decorator);
    }

    /**
     * 
     * @param Builder $query
     * @return type
     */
    protected static function getResults(Builder $query) {
        return $query->get();
    }

}

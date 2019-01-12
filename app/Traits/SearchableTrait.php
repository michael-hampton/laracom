<?php
trait SearchableTrait {
protected static function applyDecoratorsFromRequest(Request $request, Builder $query)
    {
        foreach ($request->all() as $filterName => $value) {

            if(trim($value) === '') {
                continue;
            }
            
            $decorator = static::createFilterDecorator($filterName);

            if (static::isValidDecorator($decorator)) {
                $query = $decorator::apply($query, $value);
            }

        }
        return $query;
    }
    
    protected static function createFilterDecorator($name)
    {
        return return __NAMESPACE__ . '\\Filters\\' . 
            ucfirst
                (str_replace(' ', '', 
                    ucwords(str_replace('_', ' ', $name))
                )
            );
    }
    
    protected static function isValidDecorator($decorator)
    {
        return class_exists($decorator);
    }

    protected static function getResults(Builder $query)
    {
        return $query->get();
    }
}

<?php

namespace App\Search\Filters;

use Illuminate\Database\Eloquent\Builder;

class ProductName {

    /**
     * Apply a given search value to the builder instance.
     *
     * @param Builder $builder
     * @param mixed $value
     * @return Builder $builder
     */
    public static function apply(Builder $builder, $value) {
        return $builder->where('products.name', 'LIKE', '%' . $value . '%');
    }

}

<?php

namespace App\Search\Filters;

use Illuminate\Database\Eloquent\Builder;

class Courier {

    /**
     * Apply a given search value to the builder instance.
     *
     * @param Builder $builder
     * @param mixed $value
     * @return Builder $builder
     */
    public static function apply(Builder $builder, $value) {

        return $builder->whereIn('orders.courier_id', $value);
    }

}

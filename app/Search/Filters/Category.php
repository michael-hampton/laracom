<?php
use Illuminate\Database\Eloquent\Builder;
class Category
{
    /**
     * Apply a given search value to the builder instance.
     *
     * @param Builder $builder
     * @param mixed $value
     * @return Builder $builder
     */
    public static function apply(Builder $builder, $value)
    {
        return $builder->where('category_id', $value);
    }
}

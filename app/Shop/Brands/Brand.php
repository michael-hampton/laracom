<?php

namespace App\Shop\Brands;

use App\Shop\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;
class Brand extends Model
{
    use SearchableTrait;
 
    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'brands.name' => 10
        ]
    ];
    
    protected $fillable = [
        'name',
        'cover',
        'status'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    /**
     * @param $term
     *
     * @return mixed
     */
    public function searchBrand($term)
    {
        return self::search($term);
    }
}

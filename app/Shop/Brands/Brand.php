<?php

namespace App\Shop\Brands;

use App\Shop\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;
use Watson\Validating\ValidatingTrait;

class Brand extends Model {

    use SearchableTrait,
        ValidatingTrait;

    /**
     *
     * @var type 
     */
    protected $rules = [
        'create' => [
            'name'   => ['required', 'unique:brands'],
            'status' => ['required']
        ],
        'update' => [
            'name'   => ['required'],
            'status' => ['required']
        ]
    ];

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
    public function products() {
        return $this->hasMany(Product::class);
    }

    /**
     * @param $term
     *
     * @return mixed
     */
    public function searchBrand($term) {
        return self::search($term);
    }

    /**
     * 
     * @param type $blUpdate
     * @return boolean
     */
    public function validate($blUpdate = false) {

        $rules = $blUpdate === false ? $this->rules['create'] : $this->rules['update'];
        $this->setRules($rules);
        $blValid = $this->isValid();

        if (!$blValid)
        {
            $this->validationFailures = $this->getErrors()->all();

            return false;
        }

        return true;
    }

    /**
     * 
     * @return type
     */
    public function getValidationFailures() {
        return $this->validationFailures;
    }

}

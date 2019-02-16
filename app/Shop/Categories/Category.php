<?php

namespace App\Shop\Categories;

use App\Shop\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

class Category extends Model {

    use ValidatingTrait;

    /**
     *
     * @var type 
     */
    protected $rules = [
        'create' => [
            'name'   => ['required'],
            'status' => ['required']
        ],
        'update' => [
            'name'   => ['required'],
            'status' => ['required']
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'cover',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function products() {
        return $this->belongsToMany(Product::class);
    }

    public function parent()
    {
    return $this->belongsTo(static::class, 'parent_id');


    }

public function children() {
    return $this->hasMany(static::class, 'parent_id');
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

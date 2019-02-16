<?php

namespace App\Shop\Channels;

use Illuminate\Database\Eloquent\Model;
use App\Shop\Products\Product;
use App\Shop\Employees\Employee;
use Illuminate\Support\Collection;
use Nicolaslopezj\Searchable\SearchableTrait;
use Watson\Validating\ValidatingTrait;

class Channel extends Model {

    use SearchableTrait,
        ValidatingTrait;

    /**
     *
     * @var type 
     */
    protected $rules = [
        'create' => [
            'name'               => ['required', 'unique:channels'],
            'allocate_on_order'  => ['required'],
            'backorders_enabled' => ['required'],
            'has_priority'       => ['required']
        ],
        'update' => [
            'name' => ['required']
        ]
    ];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'channels.name'        => 10,
            'channels.description' => 5
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'email',
        'cover',
        'allocate_on_order',
        'backorders_enabled',
        'send_received_email',
        'send_hung_email',
        'send_backorder_email',
        'send_dispatched_email',
        'status',
        'has_priority',
        'partial_shipment',
        'strict_validation'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 
     * @return type
     */
    public function products() {
        return $this->belongsToMany(Product::class);
    }

    public function employees() {
        return $this->belongsToMany(Employee::class);
    }

    /**
     * @param string $term
     * @return Collection
     */
    public function searchChannel(string $term): Collection {
        return self::search($term)->get();
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

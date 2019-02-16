<?php

namespace App\Shop\Addresses;

use App\Shop\Customers\Customer;
use App\Shop\Orders\Order;
use App\Shop\Provinces\Province;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Shop\Cities\City;
use App\Shop\Countries\Country;
use Sofa\Eloquence\Eloquence;
use Watson\Validating\ValidatingTrait;

class Address extends Model {

    use SoftDeletes,
        Eloquence,
        ValidatingTrait;

    /**
     *
     * @var type 
     */
    protected $rules = [
        'create' => [
            'alias'     => ['required'],
            'address_1' => ['required']
        ],
        'update' => [
            'alias'     => ['required'],
            'address_1' => ['required']
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'alias',
        'address_1',
        'address_2',
        'zip',
        'city',
        'state_code',
        'province_id',
        'country_id',
        'customer_id',
        'status',
        'phone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    protected $dates = ['deleted_at'];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function province() {
        return $this->belongsTo(Province::class);
    }

    /**
     * @deprecated
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city() {
        return $this->belongsTo(City::class, 'city');
    }

    public function orders() {
        return $this->hasMany(Order::class);
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

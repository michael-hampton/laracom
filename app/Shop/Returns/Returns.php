<?php

namespace App\Shop\Returns;

use App\Shop\Orders\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;
use Watson\Validating\ValidatingTrait;

class Returns extends Model {

    use SoftDeletes,
        Eloquence,
        ValidatingTrait;

    /**
     *
     * @var type 
     */
    protected $rules = [
        'create' => [
            'customer'       => ['required'],
            'item_condition' => ['required'],
            'resolution'     => ['required'],
        ],
        'update' => [
            'customer'       => ['required'],
            'item_condition' => ['required'],
            'resolution'     => ['required']
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'order_id',
        'item_condition',
        'resolution',
        'customer',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     *
     * @var type 
     */
    protected $dates = ['deleted_at', 'date_returned'];

    /**
     * 
     * @return type
     */
    public function order() {
        return $this->belongsTo(Order::class);
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

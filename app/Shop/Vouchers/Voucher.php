<?php

namespace App\Shop\Vouchers;

use App\Shop\Orders\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;

class Voucher extends Model {

    use SoftDeletes,
        Eloquence;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'coupon_code',
        'amount',
        'amount_type',
        'expiry_date',
        'start_date',
        'status',
        'channel'
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
    protected $dates = ['deleted_at', 'expiry_date', 'start_date'];
    
    public function getStartDateAttribute($value) {
        return date('m-d-Y', strtotime($value));
    }
    
     public function getExpiryDateAttribute($value) {
        return date('m-d-Y', strtotime($value));
    }

}

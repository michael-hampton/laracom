<?php

namespace App\Shop\VoucherCodes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;

class VoucherCode extends Model {

    use SoftDeletes,
        Eloquence;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'use_count',
        'voucher_code',
        'voucher_id',
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
    protected $dates = ['deleted_at'];

}

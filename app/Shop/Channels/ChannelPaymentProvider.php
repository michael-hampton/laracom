<?php

namespace App\Shop\Channels;

use Illuminate\Database\Eloquent\Model;
use App\Shop\Products\Product;
use App\Shop\Employees\Employee;
use Illuminate\Support\Collection;

/**
 * 
 */
class ChannelPaymentProvider extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'channel_id',
        'payment_provider_id'
    ];
    protected $table = 'channel_payment_providers';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}

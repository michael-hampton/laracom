<?php

namespace App\Shop\Channels;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 */
class ChannelWarehouse extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'channel_id',
        'warehouse_id'
    ];
    protected $table = 'channel_warehouses';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}

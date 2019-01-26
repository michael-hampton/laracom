<?php

namespace App\Shop\Channels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * 
 */
class Warehouse extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];
    protected $table = 'warehouse';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}

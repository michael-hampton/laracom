<?php

namespace App\Shop\Channels;

use Illuminate\Database\Eloquent\Model;
use App\Shop\Products\Product;
use App\Shop\Employees\Employee;
use Illuminate\Support\Collection;

/**
 * 
 */
class ChannelTemplate extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'channel_id',
        'section_id',
        'description',
        'title'
    ];
    protected $table = 'channel_templates';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}

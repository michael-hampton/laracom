<?php
namespace App\Shop\CourierRates;
use Illuminate\Database\Eloquent\Model;
class CourierRate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'range_from',
        'range_to',
        'country',
        'cost',
        'channel',
        'courier'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    
}

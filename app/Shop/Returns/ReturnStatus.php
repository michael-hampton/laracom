<?php

namespace App\Shop\Returns;

use App\Shop\Orders\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Eloquence\Eloquence;

class ReturnStatus extends Model {

    use SoftDeletes,
        Eloquence;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'id',
        'name'
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
    public function order() {
        return $this->belongsTo(Order::class);
    }

}

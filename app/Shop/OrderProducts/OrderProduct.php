<?php

namespace App\Shop\OrderProducts;

use Illuminate\Database\Eloquent\Model;
use App\Shop\Employees\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class OrderProduct extends Model {

    protected $table = 'order_product';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'product_name',
        'product_sku',
        'product_description',
        'product_price',
        'status',
        'tote',
        'sage_ref',
        'picklist_ref',
        'warehouse',
        'courier_id',
        'tracking_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order() {
        return $this->belongsTo(Order::class);
    }

    /**
     * 
     * @param array $params
     * @param int $id
     * @return type
     */
    public function doUpdate(array $params, int $id) {

        return DB::table('order_product')
                        ->where('id', $id)
                        ->update($params);
    }

}

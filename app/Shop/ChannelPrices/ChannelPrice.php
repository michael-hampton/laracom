<?php

namespace App\Shop\ChannelPrices;

use App\Shop\Products\Product;
use Illuminate\Database\Eloquent\Model;

class ChannelPrice extends Model
{
    /**
     *
     * @var type 
     */
    protected $table = 'channel_product';
    
    /**
     *
     * @var type 
     */
    protected $fillable = [
        'channel_id',
        'attribute_id',
        'product_id',
        'description',
        'price'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

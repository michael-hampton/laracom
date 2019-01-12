<?php

namespace App\Search;

use App\Shop\ChannelPrices\ChannelPrice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\SearchableTrait;

/**
 * 
 */
class OrderProductSearch implements FilterInterface {

    use SearchableTrait;

    /**
     * 
     * @param Request $filters
     * @return type
     */
    public static function apply(Request $filters) {

        $newQuery = (new \App\Shop\OrderProducts\OrderProduct)->query()
                ->select('order_product.*')
                ->join('orders', 'order_product.order_id', '=', 'orders.id')
                ->join('products', 'order_product.product_id', '=', 'products.id')
                ->join('customers', 'orders.customer_id', '=', 'customers.id');

        $query = static::applyDecoratorsFromRequest(
                        $filters, $newQuery
        );

        $query->groupBy('order_product.id');
        $query->orderBy('orders.created_at', 'DESC')->orderBy('is_priority', 'ASC');


        return static::getResults($query);
    }

}

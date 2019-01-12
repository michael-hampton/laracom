<?php

namespace App\Search;

use App\Shop\ChannelPrices\ChannelPrice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\SearchableTrait;

/**
 * 
 */
class OrderSearch implements FilterInterface {

    use SearchableTrait;

    const MODEL = App\Shop\ChannelPrices\ChannelPrice;

    /**
     * 
     * @param Request $filters
     * @return type
     */
    public static function apply(Request $filters) {

        $newQuery = (new \App\Shop\Orders\Order)->query()
                ->select('orders.*')
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->join('order_product', 'orders.id', '=', 'order_product.order_id')
                ->join('products', 'products.id', '=', 'order_product.product_id')
                ->leftJoin('voucher_codes', 'orders.voucher_code', '=', 'voucher_codes.id');

        $query = static::applyDecoratorsFromRequest(
                        $filters, $newQuery
        );

        $query->groupBy('orders.id');
        $query->orderBy('orders.created_at', 'DESC')->orderBy('is_priority', 'ASC');


        return static::getResults($query);
    }

}

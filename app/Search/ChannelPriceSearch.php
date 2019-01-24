<?php

namespace App\Search;

use App\Shop\ChannelPrices\ChannelPrice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\SearchableTrait;

/**
 * 
 */
class ChannelPriceSearch implements FilterInterface {

    use SearchableTrait;

    const MODEL = App\Shop\ChannelPrices\ChannelPrice;

    public static function apply(Request $filters) {

        $newQuery = (new \App\Shop\ChannelPrices\ChannelPrice)->query()
                ->select('channel_product.*')
                ->join('products', 'products.id', '=', 'channel_product.product_id')
                ->join('category_product', 'category_product.product_id', '=', 'channel_product.product_id')
                ->whereNull('attribute_id');

        $query = static::applyDecoratorsFromRequest(
                        $filters, $newQuery
        );

        return static::getResults($query);
    }

}

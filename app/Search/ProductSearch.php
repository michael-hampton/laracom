<?php

namespace App\Search;

use App\Shop\ChannelPrices\ChannelPrice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\SearchableTrait;

/**
 * 
 */
class ProductSearch implements FilterInterface {

    use SearchableTrait;

    const MODEL = App\Shop\ChannelPrices\ChannelPrice;

    /**
     * 
     * @param Request $filters
     * @return type
     */
    public static function apply(Request $filters) {

        $newQuery = (new \App\Shop\Products\Product)->query()
                ->select('products.*')
                ->join('category_product', 'category_product.product_id', '=', 'products.id');


        $query = static::applyDecoratorsFromRequest(
                        $filters, $newQuery
        );
        

        return static::getResults($query);
    }

}

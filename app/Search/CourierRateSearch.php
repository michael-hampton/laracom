<?php

namespace App\Search;

use App\Shop\CourierRates\CourierRate;
use Illuminate\Http\Request;
use App\Traits\SearchableTrait;

/**
 * 
 */
class CourierRateSearch implements FilterInterface {

    use SearchableTrait;

    const MODEL = App\Shop\ChannelPrices\ChannelPrice;

    /**
     * 
     * @param Request $filters
     * @return type
     */
    public static function apply(Request $filters) {

        $query = static::applyDecoratorsFromRequest(
                        $filters, (new \App\Shop\CourierRates\CourierRate)->newQuery()
        );

        return static::getResults($query);
    }

}

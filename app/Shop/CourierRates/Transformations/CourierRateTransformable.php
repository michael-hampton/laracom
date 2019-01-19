<?php

namespace App\Shop\CourierRates\Transformations;

use App\Shop\CourierRates\CourierRate;
use App\Shop\Couriers\Courier;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\Channels\Channel;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Countries\Country;
use App\Shop\Countries\Repositories\CountryRepository;
use Illuminate\Support\Facades\Storage;

trait CourierRateTransformable {

    /**
     * Transform the product
     *
     * @param Product $product
     * @return Product
     */
    protected function transformCourierRate(CourierRate $courierRate) {

        $courierRepo = new CourierRepository(new Courier);
        $courier = $courierRepo->findCourierById($courierRate->courier);
        
      

        $channelRepo = new ChannelRepository(new Channel);
        $channel = $channelRepo->findChannelById($courierRate->channel);

        $countryRepo = new CountryRepository(new Country);
        $country = $countryRepo->findCountryById($courierRate->country);
        
          

        $courierObj = new CourierRate;
        $courierObj->id = (int) $courierRate->id;
        $courierObj->country = $courierRate->country;
        $courierObj->range_from = $courierRate->range_from;
        $courierObj->range_to = $courierRate->range_to;
        $courierObj->cost = $courierRate->cost;
        $courierObj->courier_name = $courier->name;
        $courierObj->courier = $courier->id;
        $courierObj->channel_name = $channel->name;
         $courierObj->channel = $channel->id;
        $courierObj->country = $country->name;
        
       
        return $courierObj;
    }

}

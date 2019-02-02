<?php

namespace App\Shop\Products\Transformations;

use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Brands\Brand;
use App\Shop\ChannelPrices\ChannelPrice;
use Illuminate\Support\Facades\Storage;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepository;
use App\Shop\Products\Product;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Channels\Channel;

trait ProductCsvTransformable {

    /**
     * 
     * @param ChannelPrice $product
     * @return type
     */
    protected function transformProductForCsv(Product $product) {

        if (!empty(env('CHANNEL'))) {
            $channelRepo = new ChannelRepository(new Channel);
            $channel = $channelRepo->findByName(env('CHANNEL'));
        }

        $price = $product->price;


        if (isset($channel) && !empty($channel) && !empty($channel->id)) {
            $channelPriceRepo = new ChannelPriceRepository(new \App\Shop\ChannelPrices\ChannelPrice);
            $channelPrice = $channelPriceRepo->getChannelProduct($product, $channel);

            $price = !empty($channelPrice) ? $channelPrice->price : $product->price;
        }

        $brandId = $product->brand_id;

        $brandRepo = new BrandRepository(new Brand);
        $brand = $brandRepo->findBrandById($brandId);

        $arrProducts = array(
            'id' => $product->id,
            'name' => $product->name,
            'description' => strip_tags($product->description),
            'brand_name' => $brand->name,
            'sku' => $product->sku,
            'quantity' => $product->quantity,
            'reserved_stock' => $product->reserved_stock,
            'status' => $product->status,
            'price' => !empty($price) ? $price : $product->price,
            'cost_price' => $product->cost_price,
            'sale_price' => $product->sale_price,
            'weight' => (float) $product->weight
        );

        return $arrProducts;
    }

}

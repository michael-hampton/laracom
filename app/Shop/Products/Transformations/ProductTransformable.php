<?php

namespace App\Shop\Products\Transformations;

use App\Shop\Products\Product;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepository;
use App\Shop\Channels\Repositories\ChannelRepository;
use Illuminate\Support\Facades\Storage;

trait ProductTransformable {

    /**
     * Transform the product
     *
     * @param Product $product
     * @return Product
     */
    protected function transformProduct(Product $product) {

        $channelRepo = new ChannelRepository(new \App\Shop\Channels\Channel);
        $channel = $channelRepo->listChannels()->where('name', env('CHANNEL'))->first();

        if (!empty($channel) && !empty($channel->id)) {
            $channelPriceRepo = new ChannelPriceRepository(new \App\Shop\ChannelPrices\ChannelPrice);
            $channelPrice = $channelPriceRepo->listChannelPrices()->where('product_id', $product->id)->where('channel_id', $channel->id);
            $price = !empty($channelPrice[0]) ? $channelPrice[0]->price : $product->price;
        }




        $prod = new Product;
        $prod->id = (int) $product->id;
        $prod->name = $product->name;
        $prod->sku = $product->sku;
        $prod->slug = $product->slug;
        $prod->description = $product->description;
        $prod->cover = asset("storage/$product->cover");
        $prod->quantity = $product->quantity;
        $prod->price = isset($price) ? $price : $product->price;
        $prod->status = $product->status;
        $prod->weight = (float) $product->weight;
        $prod->mass_unit = $product->mass_unit;
        $prod->sale_price = $product->sale_price;
        $prod->brand_id = (int) $product->brand_id;
        return $prod;
    }

}

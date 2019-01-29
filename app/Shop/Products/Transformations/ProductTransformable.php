<?php

namespace App\Shop\Products\Transformations;

use App\Shop\Products\Product;
use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Brands\Brand;
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

        if (!empty(env('CHANNEL'))) {
            $channelRepo = new ChannelRepository(new \App\Shop\Channels\Channel);
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

        $prod = new Product;
        $prod->id = (int) $product->id;
        $prod->name = $product->name;
        $prod->sku = $product->sku;
        $prod->slug = $product->slug;
        $prod->description = $product->description;
        $prod->cover = asset("storage/$product->cover");
        $prod->quantity = $product->quantity;
        $prod->reserved_stock = $product->reserved_stock;
        $prod->price = isset($price) ? $price : $product->price;
        $prod->status = $product->status;
        $prod->weight = (float) $product->weight;
        $prod->mass_unit = $product->mass_unit;
        $prod->sale_price = $product->sale_price;
        $prod->cost_price = $product->cost_price;
        $prod->brand_id = (int) $product->brand_id;
        $prod->brand_name = $brand->name;
        return $prod;
    }

}

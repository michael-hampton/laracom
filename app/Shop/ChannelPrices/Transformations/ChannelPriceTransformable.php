<?php

namespace App\Shop\ChannelPrices\Transformations;

use App\Shop\ChannelPrices\ChannelPrice;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Brands\Brand;
use Illuminate\Support\Facades\Storage;

trait ChannelPriceTransformable {

    /**
     * Transform the product
     *
     * @param Product $product
     * @return Product
     */
    protected function transformProduct(ChannelPrice $channelPrice) {

        $chann = new ChannelPrice;
        $chann->channel_id = (int) $channelPrice->channel_id;
        $chann->product_id = $channelPrice->product_id;
        $chann->price = $channelPrice->price;
        $chann->id = $channelPrice->id;


        $productRepo = new ProductRepository(new Product);
        $product = $productRepo->findProductById($chann->product_id);

        $brandRepo = new BrandRepository(new Brand);
        $brand = $brandRepo->findBrandById($product->brand_id);

        $chann->name = $product->name;
        $chann->description = $product->description;
        $chann->brand_name = $brand->name;
        $chann->sku = $product->sku;
        $chann->quantity = $product->quantity;
        $chann->cover = $product->cover;
        $chann->status = $product->status;
        $chann->description = $product->description;


        return $chann;
    }

}

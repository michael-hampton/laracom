<?php

namespace App\Shop\ChannelPrices\Transformations;

use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Product;
use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Brands\Brand;
use App\Shop\ChannelPrices\ChannelPrice;
use Illuminate\Support\Facades\Storage;

trait ProductCsvTransformable {

    /**
     * 
     * @param ChannelPrice $product
     * @return type
     */
    protected function transformProductForCsv(ChannelPrice $channelProduct) {

        $productRepo = new ProductRepository(new Product);
        $product = $productRepo->findProductById($channelProduct->product_id);

        $price = $channelProduct->price;

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

<?php

namespace App\Http\Controllers\Front;

use App\Shop\Products\Product;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepository;
use App\Shop\ChannelPrices\ChannelPrice;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Channels\Channel;
use App\Http\Controllers\Controller;
use App\Shop\Products\Transformations\ProductTransformable;

class ProductController extends Controller {

    use ProductTransformable;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * ProductController constructor.
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository) {
        $this->productRepo = $productRepository;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search() {

//        $channel = env('CHANNEL');
//        
//        $objChannel = (new ChannelRepository(new Channel))->listChannels()->where('name', $channel)->first();
//        
//        $repo = new ChannelRepository($objChannel);
//
//        $products = $repo->findProducts()->where('status', 1)->all();

        if (request()->has('q') && request()->input('q') != '') {
            $list = $this->productRepo->searchProduct(request()->input('q'));
        } else {
            $list = $this->productRepo->listProducts();
        }

        $products = $list->where('status', 1)->map(function (Product $item) {
            return $this->transformProduct($item);
        });

        return view('front.products.product-search', [
            'products' => $this->productRepo->paginateArrayResults($products->all(), 10)
        ]);
    }

    /**
     * Get the product
     *
     * @param string $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(string $slug) {

        $objChannelPriceRepository = new ChannelPriceRepository(new ChannelPrice);
        $product = $this->productRepo->findProductBySlug(['slug' => $slug]);
        $channel = (new ChannelRepository(new Channel))->findByName(env('CHANNEL'));
        $channelProduct = $objChannelPriceRepository->getChannelProduct($product, $channel);

        if (!empty($channelProduct) && !empty($channelProduct->price)) {
            $product->price = $channelProduct->price;
        }

        $images = $product->images()->get();
        $category = $product->categories()->first();

        $productAttributes = $product->attributes;
        $channelAttributes = $objChannelPriceRepository->getAttributesByParentProduct($product, $channel);

        return view('front.products.product', compact(
                        'product', 'images', 'productAttributes', 'category', 'combos', 'channelAttributes'
        ));
    }

}

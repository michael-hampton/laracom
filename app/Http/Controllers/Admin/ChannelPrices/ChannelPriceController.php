<?php

namespace App\Http\Controllers\Admin\ChannelPrices;

use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepositoryInterface;
use App\Shop\ChannelPrices\ChannelPrice;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepository;
use App\Shop\ChannelPrices\Requests\UpdateChannelPriceRequest;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Categories\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Shop\Brands\Repositories\BrandRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Shop\ChannelPrices\Transformations\ChannelPriceTransformable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Search\ChannelPriceSearch;

class ChannelPriceController extends Controller {

    use ChannelPriceTransformable;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepo;

    /**
     * @var ChannelPriceRepositoryInterface
     */
    private $channelPriceRepo;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepo;

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepo;

    /**
     * 
     * @param ProductRepositoryInterface $productRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param ChannelPriceRepositoryInterface $channelPriceRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
    ProductRepositoryInterface $productRepository, ChannelRepositoryInterface $channelRepository, ChannelPriceRepositoryInterface $channelPriceRepository, CategoryRepositoryInterface $categoryRepository, BrandRepositoryInterface $brandRepository
    ) {
        $this->productRepo = $productRepository;
        $this->channelRepo = $channelRepository;
        $this->channelPriceRepo = $channelPriceRepository;
        $this->categoryRepo = $categoryRepository;
        $this->brandRepo = $brandRepository;

//        $this->middleware(['permission:create-product, guard:employee'], ['only' => ['create', 'store']]);
//        $this->middleware(['permission:update-product, guard:employee'], ['only' => ['edit', 'update']]);
//        $this->middleware(['permission:delete-product, guard:employee'], ['only' => ['destroy']]);
//        $this->middleware(['permission:view-product, guard:employee'], ['only' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($channel) {


        $channel = $this->channelRepo->findByName($channel);
        $channels = $this->channelRepo->listChannels('name', 'asc');
        $categories = $this->categoryRepo->listCategories('name', 'asc')->where('parent_id', 1);
        $brands = $this->brandRepo->listBrands();
        $list = $this->channelPriceRepo->listChannelPrices()->where('channel_id', $channel->id);

        $products = $list->map(function (ChannelPrice $item) {

                    return $this->transformProduct($item);
                })->all();

        return view('admin.channel-price.list', [
            'products' => $this->channelPriceRepo->paginateArrayResults($products, 10),
            'channel' => $channel,
            'categories' => $categories,
            'channels' => $channels,
            'brands' => $brands
        ]);
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function search(Request $request) {

        $list = ChannelPriceSearch::apply($request);

        $products = $list->map(function (ChannelPrice $item) {

                    return $this->transformProduct($item);
                })->all();

        $categories = $this->categoryRepo->listCategories('name', 'asc')->where('parent_id', 1);
        $channels = $this->channelRepo->listChannels('name', 'asc');
        $brands = $this->brandRepo->listBrands();

        return view('admin.channel-price.list', [
            'categories' => $categories,
            'channels' => $channels,
            'brands' => $brands,
            'products' => $this->channelPriceRepo->paginateArrayResults($products, 10)
                ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id) {
        $product = $this->channelPriceRepo->findProductById($id);

        return view('admin.products.show', [
            'product' => $product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {


        $channelPrice = $this->channelPriceRepo->findChannelPriceById($id);

        return view('admin.channel-price.edit', [
            'channelPrice' => $channelPrice
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateProductRequest $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     * @throws \App\Shop\Products\Exceptions\ProductUpdateErrorException
     */
    public function update(UpdateChannelPriceRequest $request, int $id) {
        $channelPrice = $this->channelPriceRepo->findChannelPriceById($id);
        $channelPriceRepo = new ChannelPriceRepository($channelPrice);


        $data = $request->except('_token', '_method');


        $channelPriceRepo->updateChannelPrice($data);

        return redirect()->route('admin.channel-prices.edit', $id)
                        ->with('message', 'Update successful');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $channelPrice = $this->channelPriceRepo->findChannelPriceById($id);
        $channel = $this->channelRepo->findChannelById($channelPrice->channel_id);
        $this->channelPriceRepo->delete($id);

        return \Redirect::route('admin.channel-prices.index', $channel->name)->with('message', 'Delete successful');
    }

}

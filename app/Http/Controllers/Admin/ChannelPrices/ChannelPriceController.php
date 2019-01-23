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
use Illuminate\Support\MessageBag;
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

        return view('admin.channel-price.search', [
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
        $product = $this->productRepo->findProductById($channelPrice->product_id);
        $attributes = (new \App\Shop\ProductAttributes\Repositories\ProductAttributeRepository(new \App\Shop\ProductAttributes\ProductAttribute))->getAttributesForProduct($product);
        
        return view('admin.channel-price.edit', [
            'attributes' => $attributes,
            'channelPrice' => $channelPrice,
            'product' => $product
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
    public function update(Request $request, int $id) {

        $channelPrice = $this->channelPriceRepo->findChannelPriceById($id);
        $channelPriceRepo = new ChannelPriceRepository($channelPrice);

        $data = $request->except('_token', '_method');

        $validator = Validator::make($data, (new UpdateChannelPriceRequest())->rules());

        // Validate the input and return correct response
        if ($validator->fails()) {
            echo json_encode(array(
                'http_code' => 400,
                'errors' => $validator->getMessageBag()->toArray()
            ));
            die;
        }


        $channelPriceRepo->updateChannelPrice($data);

        echo json_encode(array(
            'http_code' => 200,
            'message' => 'Product updated successfully'
        ));
        die;
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

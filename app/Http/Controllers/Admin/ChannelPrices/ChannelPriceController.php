<?php

namespace App\Http\Controllers\Admin\ChannelPrices;

use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepositoryInterface;
use App\Shop\ChannelPrices\ChannelPrice;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepository;
use App\Shop\ChannelPrices\Requests\UpdateChannelPriceRequest;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Shop\ChannelPrices\Transformations\ChannelPriceTransformable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
     * ProductController constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(
    ProductRepositoryInterface $productRepository, ChannelRepositoryInterface $channelRepository, ChannelPriceRepositoryInterface $channelPriceRepository
    ) {
        $this->productRepo = $productRepository;
        $this->channelRepo = $channelRepository;
        $this->channelPriceRepo = $channelPriceRepository;

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


        $channel = $this->channelRepo->listChannels()->where('name', $channel)->first();
        $list = $this->channelPriceRepo->listChannelPrices()->where('channel_id', $channel->id);


//        if (request()->has('q') && request()->input('q') != '') {
//            $list = $this->productRepo->searchProduct(request()->input('q'));
//        }

        $products = $list->map(function (ChannelPrice $item) {

                    return $this->transformProduct($item);
                })->all();

        return view('admin.channel-price.list', [
            'products' => $this->channelPriceRepo->paginateArrayResults($products, 10),
            'channel' => $channel
        ]);
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

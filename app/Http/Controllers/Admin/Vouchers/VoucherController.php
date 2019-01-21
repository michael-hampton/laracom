<?php

namespace App\Http\Controllers\Admin\Vouchers;

use App\Shop\Vouchers\Voucher;
use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Vouchers\Repositories\VoucherRepository;
use App\Shop\Vouchers\VoucherGenerator;
use App\Shop\Vouchers\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Shop\Categories\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Shop\Brands\Repositories\Interfaces\BrandRepositoryInteface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Vouchers\Requests\CreateVoucherRequest;
use App\Shop\Vouchers\Requests\UpdateVoucherRequest;
use App\Shop\Vouchers\Transformations\VoucherTransformable;
use App\Http\Controllers\Controller;

class VoucherController extends Controller {

    use VoucherTransformable;

    /**
     *
     * @var VoucherRepositoryInterface $voucherRepo
     */
    private $voucherRepo;

    /**
     *
     * @var BrandRepositoryInterface $channelRepo 
     */
    private $brandRepo;

    /**
     *
     * @var CategoryRepositoryInterface $channelRepo 
     */
    private $categoryRepo;

    /**
     *
     * @var ProductRepositoryInterface $channelRepo 
     */
    private $productRepo;

    /**
     *
     * @var ChannelRepositoryInterface $channelRepo 
     */
    private $channelRepo;

    /**
     * 
     * @param VoucherRepositoryInterface $voucherRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param BrandRepository $brandRepository
     * @param \App\Http\Controllers\Admin\Vouchers\CategoryRepositoryInterface $categoryRepository
     * @param \App\Http\Controllers\Admin\Vouchers\ProductRepositoryInterface $productRepository
     */
    public function __construct(
    VoucherRepositoryInterface $voucherRepository, ChannelRepositoryInterface $channelRepository, BrandRepository $brandRepository, CategoryRepositoryInterface $categoryRepository, ProductRepositoryInterface $productRepository
    ) {
        $this->voucherRepo = $voucherRepository;
        $this->channelRepo = $channelRepository;
        $this->brandRepo = $brandRepository;
        $this->categoryRepo = $categoryRepository;
        $this->productRepo = $productRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $list = $this->voucherRepo->listVoucher('expiry_date', 'desc');

        if (request()->has('q')) {
            $list = $this->voucherRepo->searchVoucher(request()->input('q'));
        }

        $vouchers = $this->mapVouchers($list);

        return view('admin.vouchers.list', ['vouchers' => $this->voucherRepo->paginateArrayResults($vouchers)]);
    }

    /**
     * 
     * @param array $list
     * @return type
     */
    private function mapVouchers($list) {
        $vouchers = $list->map(function (Voucher $voucher) {
                    return $this->transformVoucher($voucher);
                })->all();

        return $vouchers;
    }

    /**
     * 
     * @param type $channel
     */
    public function getVouchersByChannel($channel) {

        $channel = $this->channelRepo->listChannels()->where('name', $channel)->first();

        $list = $this->voucherRepo->listVoucher('expiry_date', 'desc')->where('channel', $channel->id);

        $vouchers = $this->mapVouchers($list);

        return view('admin.vouchers.list', ['vouchers' => $this->voucherRepo->paginateArrayResults($vouchers)]);
    }

    /**
     * Show the form for creating a new resource.
     * @param type $channel
     * @return type
     */
    public function create($channel = null) {


        if (!is_null($channel)) {
            $channels = null;
            $channel = $this->channelRepo->listChannels()->where('name', $channel)->first();
            $repo = new ChannelRepository($channel);

            $products = $repo->findProducts()->where('status', 1)->all();
        } else {
            $channels = $this->channelRepo->listChannels();
            $products = $this->productRepo->listProducts()->where('status', 1);
        }

        $scopes = !empty(env('VOUCHER_SCOPES')) ? explode(',', env('VOUCHER_SCOPES')) : [];

        return view('admin.vouchers.create', [
            'selectedChannel' => isset($channel) ? $channel->id : null,
            'channels' => $channels,
            'scopes' => $scopes,
            'products' => $products,
            'brands' => $this->brandRepo->listBrands(),
            'categories' => $this->categoryRepo->listCategories('parent_id', 1)
                ]
        );
    }

    /**
     *  Store a newly created resource in storage.
     * 
     * @param CreateVoucherRequest $request
     * @return type
     */
    public function store(Request $request) {

        $request->request->add(['expiry_date' => date('Y-m-d', strtotime($request->expiry_date))]); //add request
        $request->request->add(['start_date' => date('Y-m-d', strtotime($request->start_date))]);

          $validator = Validator::make($reqest, (new CreateVoucherRequest())->rules());
        // Validate the input and return correct response
        if ($validator->fails()) {
           echo json_encode(array(
                        'http_code' => 400,
                        'errors' => $validator->getMessageBag()->toArray()
            ));
           die;
        }
        
        $voucher = $this->voucherRepo->createVoucher($request->except('_token', '_method'));

        (new VoucherGenerator())->createVoucher($voucher, $request->use_count, $request->quantity);

        $request->session()->flash('message', 'Creation successful');
        return redirect()->route('admin.vouchers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id) {
        return view('admin.vouchers.show', ['voucher' => $this->voucherRepo->findVoucherById($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {

        $voucher = $this->voucherRepo->findVoucherById($id);
        $channel = $voucher->channel;

        if (!empty($channel)) {
            $objChannel = $this->channelRepo->findChannelById($channel);
            $repo = new ChannelRepository($objChannel);

            $products = $repo->findProducts()->where('status', 1)->all();
        } else {
            $products = $this->productRepo->listProducts()->where('status', 1);
        }

        $scopes = !empty(env('VOUCHER_SCOPES')) ? explode(',', env('VOUCHER_SCOPES')) : [];

        return view('admin.vouchers.edit', [
            'voucher' => $voucher,
            'selectedChannel' => $channel,
            'scopes' => $scopes,
            'products' => $products,
            'brands' => $this->brandRepo->listBrands(),
            'categories' => $this->categoryRepo->listCategories('parent_id', 1)
        ]);
    }

    /**
     * 
     * @param UpdateVoucherRequest $request
     * @param int $id
     * @return \Illuminate\Http\ResponseUpdate
     * @param UpdateVoucherRequest $request
     * @param int $id
     * @return \Illuminate\Http\ResponseUpdate the specified resource in storage.
     *
     * @param  UpdateVoucherRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $voucher = $this->voucherRepo->findVoucherById($id);

        $channel = $this->channelRepo->findChannelById($voucher->channel);

        $request->request->add(['expiry_date' => date('Y-m-d', strtotime($request->expiry_date))]); //add request
        $request->request->add(['start_date' => date('Y-m-d', strtotime($request->start_date))]);

         $validator = Validator::make($reqest, (new UpdateVoucherRequest())->rules());
        // Validate the input and return correct response
        if ($validator->fails()) {
           echo json_encode(array(
                        'http_code' => 400,
                        'errors' => $validator->getMessageBag()->toArray()
            ));
           die;
        }
        
        $update = new VoucherRepository($voucher);
        $update->updateVoucher($request->except('_method', '_token'));

        $request->session()->flash('message', 'Update successful');
        return redirect()->route('admin.vouchers.edit', array('id' => $id, 'channel' => $channel->name));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $voucher = $this->voucherRepo->findVoucherById($id);
        $delete = new VoucherRepository($voucher);
        $delete->deleteVoucher();

        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.vouchers.index');
    }

}

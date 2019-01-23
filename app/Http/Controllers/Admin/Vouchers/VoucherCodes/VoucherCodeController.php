<?php

namespace App\Http\Controllers\Admin\VoucherCodes;

use App\Shop\VoucherCodes\VoucherCode;
use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\VoucherCodes\VoucherCodeGenerator;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\VoucherCodes\Requests\CreateVoucherCodeRequest;
use App\Shop\VoucherCodes\Requests\UpdateVoucherCodeRequest;
use App\Shop\VoucherCodes\Transformations\VoucherCodeTransformable;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Product;
use App\Shop\Products\Transformations\ProductTransformable;
use Gloudemans\Shoppingcart\CartItem;
use App\Shop\Carts\Repositories\CartRepository;
use App\Shop\Carts\ShoppingCart;
use App\Shop\Channels\Channel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoucherCodeController extends Controller {

    use VoucherCodeTransformable;
    use ProductTransformable;

    private $voucherCodeRepo;

    /**
     * 
     * @param VoucherRepositoryInterface $voucherRepository
     */
    public function __construct(
    VoucherCodeRepositoryInterface $voucherCodeRepository
    ) {
        $this->voucherCodeRepo = $voucherCodeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $list = $this->voucherCodeRepo->listVoucherCode('id');

        if (request()->has('q')) {
            $list = $this->voucherCodeRepo->searchVoucherCode(request()->input('q'));
        }

        $voucherCodes = $list->map(function (VoucherCode $voucherCodes) {
                    return $this->transformVoucherCode($voucherCodes);
                })->all();

        return view('admin.voucher-codes.list', ['voucherCodes' => $this->voucherCodeRepo->paginateArrayResults($voucherCodes)]);
    }

    public function getCodesByBatch() {

        $list = $this->voucherCodeRepo->listVoucherCode('id');

        if (request()->has('q')) {
            $list = $this->voucherCodeRepo->searchVoucherCode(request()->input('q'));
        }

        $voucherCodes = $list->map(function (VoucherCode $voucherCodes) {
                    return $this->transformVoucherCode($voucherCodes);
                })->all();

        return view('admin.voucher-codes.list', ['voucherCodes' => $this->voucherCodeRepo->paginateArrayResults($voucherCodes)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param type $id
     * @return \Illuminate\Http\Response
     */
    public function create($id) {

        return view('admin.voucher-codes.create', ['voucher_id' => $id]);
    }

    private function getRandomString($length = 12) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     *  Store a newly created resource in storage.
     * 
     * @param CreateVoucherRequest $request
     * @return type
     */
    public function store(Request $request) {

        $data = $request->except('_token', '_method');
        $data['voucher_code'] = $this->getRandomString();

        $validator = Validator::make($data, (new CreateVoucherCodeRequest())->rules());

        // Validate the input and return correct response
        if ($validator->fails()) {
            return response()->json(['http_code' => 400, 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $this->voucherCodeRepo->createVoucherCode($data);

        return response()->json(['http_code' => 200, 'voucher_code' => $data['voucher_code']]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id) {
        return view('admin.voucher-codes.show', ['voucher' => $this->voucherCodeRepo->findVoucherCodeById($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {

        $voucher = $this->voucherCodeRepo->findVoucherCodeById($id);

        return view('admin.voucher-codes.edit', [
            'voucherCode' => $voucher,
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
    public function update(UpdateVoucherCodeRequest $request, $id) {
        $voucherCode = $this->voucherCodeRepo->findVoucherCodeById($id);

        $update = new VoucherCodeRepository($voucherCode);
        $update->updateVoucherCode($request->except('_method', '_token'));

        $request->session()->flash('message', 'Update successful');
        return redirect()->route('admin.voucher-codes.edit', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {

        $voucherCode = $this->voucherCodeRepo->findVoucherCodeById($id);
        $delete = new VoucherCodeRepository($voucherCode);
        $delete->deleteVoucherCode();

        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.voucher-codes.index');
    }

    /**
     * 
     * @param type $voucherCode
     */
    public function validateVoucherCode($voucherCode) {

        $cartRepo = new CartRepository(new ShoppingCart);

        $cartProducts = $cartRepo->getCartItems()->map(function (CartItem $item) {
            $productRepo = new ProductRepository(new Product());
            $product = $productRepo->findProductById($item->id);
            $item->product = $this->transformProduct($product);
            $item->cover = $product->cover;
            return $item;
        });

        $channelRepo = new ChannelRepository(new Channel);
        $channel = $channelRepo->findByName(env('CHANNEL'));

        $voucherCode = $this->voucherCodeRepo->validateVoucherCode($channel, $voucherCode, $cartProducts);

        if (!$voucherCode) {

            $arrErrors = $this->voucherCodeRepo->getValidationFailures();

            if (!empty($arrErrors)) {

                return response()->json(['error' => implode('<br>', $arrErrors)], 404); // Status code here
            }

            return response()->json(['error' => 'Voucher could not be found'], 404);
        }

        $voucherCode->use_count = $voucherCode->use_count - 1;
        $voucherCode->save();
    }

}

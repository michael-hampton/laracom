<?php

namespace App\Http\Controllers\Front;

use App\Shop\Carts\Requests\AddToCartRequest;
use App\Shop\Carts\Repositories\Interfaces\CartRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\ProductAttributes\Repositories\ProductAttributeRepositoryInterface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Products\Transformations\ProductTransformable;
use App\Shop\ChannelPrices\ChannelPrice;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepository;
use App\Shop\Channels\Channel;
use App\Shop\Channels\Repositories\ChannelRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Gloudemans\Shoppingcart\CartItem;
use App\Shop\Vouchers\Repositories\VoucherRepository;
use App\Shop\Vouchers\Voucher;

class CartController extends Controller {

    use ProductTransformable;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepo;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * @var CourierRepositoryInterface
     */
    private $courierRepo;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepo;

    /**
     * @var VoucherCodeRepositoryInterface
     */
    private $voucherCodeRepo;

    /**
     * CartController constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CourierRepositoryInterface $courierRepository
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
    CartRepositoryInterface $cartRepository, ProductRepositoryInterface $productRepository, CourierRepositoryInterface $courierRepository, ProductAttributeRepositoryInterface $productAttributeRepository, VoucherCodeRepositoryInterface $voucherCodeRepository
    ) {
        $this->cartRepo = $cartRepository;
        $this->productRepo = $productRepository;
        $this->courierRepo = $courierRepository;
        $this->productAttributeRepo = $productAttributeRepository;
        $this->voucherCodeRepo = $voucherCodeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $voucher = null;

        if (request()->session()->has('voucherCode')) {
            $voucher = $this->voucherCodeRepo->getByVoucherCode(request()->session()->get('voucherCode', 1));
        }

        $courier = $this->courierRepo->findCourierById(request()->session()->get('courierId', 1));
        $shippingFee = $this->cartRepo->getShippingFee($courier);
        return view('front.carts.cart', [
            'cartItems' => $this->cartRepo->getCartItemsTransformed(),
            'subtotal' => $this->cartRepo->getSubTotal(),
            'tax' => $this->cartRepo->getTax(),
            'shippingFee' => $shippingFee,
            'total' => $this->cartRepo->getTotal(2, $shippingFee, $voucher)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  AddToCartRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(AddToCartRequest $request) {

        $product = $this->productRepo->findProductById($request->input('product'));

        if (!empty(env('CHANNEL'))) {
            $channel = (new ChannelRepository(new Channel))->findByName(env('CHANNEL'));
            $channelAttributes = (new ChannelPriceRepository(new ChannelPrice))->getAttributesByParentProduct($product, $channel);
        }


        if ($product->attributes()->count() > 0) {
            $productAttr = $product->attributes()->where('default', 1)->first();

            if (isset($channelAttributes[$productAttr->id]) && !empty($channelAttributes[$productAttr->id]->price)) {
                $product->price = $channelAttributes[$productAttr->id]->price;
            } elseif (isset($productAttr->sale_price)) {
                $product->price = $productAttr->price;
                if (!is_null($productAttr->sale_price)) {
                    $product->price = $productAttr->sale_price;
                }
            }
        }

        $options = [];

        if ($request->has('productAttribute')) {
            $attr = $this->productAttributeRepo->findProductAttributeById($request->input('productAttribute'));

            $product->price = isset($channelAttributes[$attr->id]) &&
                    !empty($channelAttributes[$attr->id]->price) ? $channelAttributes[$attr->id]->price : $attr->price;


            $options['product_attribute_id'] = $request->input('productAttribute');
            $options['combination'] = $attr->attributesValues->toArray();
        }

        $this->cartRepo->addToCart($product, $request->input('quantity'), $options);

        return redirect()->route('cart.index')
                        ->with('message', 'Add to cart successful');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $this->cartRepo->updateQuantityInCart($id, $request->input('quantity'));
        request()->session()->flash('message', 'Update cart successful');
        return redirect()->route('cart.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $this->cartRepo->removeToCart($id);
        request()->session()->flash('message', 'Removed to cart successful');
        return redirect()->route('cart.index');
    }

    public function validateVoucherCode($voucherCode) {

//        if (session()->has('voucherCode')) {
//            return response()->json(['http_code' => 400, 'errors' => ['voucher code has already been set']]); // Status code here
//        }

        $cartProducts = $this->cartRepo->getCartItems()->map(function (CartItem $item) {
            $product = $this->productRepo->findProductById($item->id);
            $item->product = $this->transformProduct($product);
            return $item;
        });

        $channelRepo = new ChannelRepository(new Channel);
        $channel = $channelRepo->findByName(env('CHANNEL'));

        $voucherRepo = new VoucherRepository(new Voucher);


        $voucherCode = $this->voucherCodeRepo->validateVoucherCode($channel, $voucherCode, $cartProducts, $voucherRepo);

        if (!$voucherCode) {

            $arrErrors = $this->voucherCodeRepo->getValidationFailures();

            if (!empty($arrErrors)) {

                return response()->json(['http_code' => 400, 'errors' => $arrErrors]); // Status code here
            }

            return response()->json(['http_code' => 400, 'errors' => ['Voucher could not be found']]);
        }

        $voucherCode->use_count = $voucherCode->use_count - 1;
        $voucherCode->save();
        return response()->json(['http_code' => 200]);
    }

}

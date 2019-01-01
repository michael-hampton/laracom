<?php

namespace App\Http\Controllers\Front;

use App\Shop\Carts\Requests\AddToCartRequest;
use App\Shop\Carts\Repositories\Interfaces\CartRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Vouchers\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Transformations\ProductTransformable;
use Gloudemans\Shoppingcart\CartItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
     * @var VoucherRepositoryInterface
     */
    private $voucherRepo;

    /**
     * CartController constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param CourierRepositoryInterface $courierRepository
     * @param \App\Http\Controllers\Front\VoucherRepositoryInterface $voucherRepository
     */
    public function __construct(
    CartRepositoryInterface $cartRepository, ProductRepositoryInterface $productRepository, CourierRepositoryInterface $courierRepository, VoucherRepositoryInterface $voucherRepository
    ) {
        $this->cartRepo = $cartRepository;
        $this->productRepo = $productRepository;
        $this->courierRepo = $courierRepository;
        $this->voucherRepo = $voucherRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        
        //request()->session()->forget('voucherCode');
        
        request()->session()->put('voucherCode', 71);
        
        $cartProducts = $this->cartRepo->getCartItems()->map(function (CartItem $item) {
            $productRepo = new ProductRepository(new Product());
            $product = $productRepo->findProductById($item->id);
            $item->product = $this->transformProduct($product);
            $item->cover = $product->cover;
            return $item;
        });

        $voucher = null;

        if (request()->session()->has('voucherCode')) {
            $voucher = $this->voucherRepo->findVoucherById(request()->session()->get('voucherCode', 1));
        }

        $courier = $this->courierRepo->findCourierById(request()->session()->get('courierId', 1));
        $shippingFee = $this->cartRepo->getShippingFee($courier);

        return view('front.carts.cart', [
            'products' => $cartProducts,
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
        $this->cartRepo->addToCart($product, $request->input('quantity'));

        $request->session()->flash('message', 'Add to cart successful');
        return redirect()->route('cart.index');
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

}

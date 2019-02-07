<?php

namespace App\Http\Controllers\Front;

use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Cart\Requests\CartCheckoutRequest;
use App\Shop\Carts\Repositories\Interfaces\CartRepositoryInterface;
use App\Shop\Carts\Requests\PayPalCheckoutExecutionRequest;
use App\Shop\Carts\Requests\StripeExecutionRequest;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\CourierRates\Repositories\CourierRateRepository;
use App\Shop\CourierRates\CourierRate;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Channels\Channel;
use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\VoucherCodes\VoucherCode;
use App\Shop\Customers\Customer;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\PaymentMethods\Paypal\Exceptions\PaypalRequestError;
use App\Shop\PaymentMethods\Paypal\Repositories\PayPalExpressCheckoutRepository;
use App\Shop\PaymentMethods\Stripe\Exceptions\StripeChargingErrorException;
use App\Shop\PaymentMethods\Stripe\StripeRepository;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Products\Transformations\ProductTransformable;
use App\Shop\Shipping\ShippingInterface;
use Exception;
use App\Http\Controllers\Controller;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PayPal\Exception\PayPalConnectionException;

class CheckoutController extends Controller {

    use ProductTransformable;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepo;

    /**
     * @var CourierRepositoryInterface
     */
    private $courierRepo;

    /**
     * @var VoucherCodeRepositoryInterface
     */
    private $voucherCodeRepo;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepo;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepo;

    /**
     * @var PayPalExpressCheckoutRepository
     */
    private $payPal;

    /**
     * @var ShippingInterface
     */
    private $shippingRepo;

    public function __construct(
    CartRepositoryInterface $cartRepository, CourierRepositoryInterface $courierRepository, AddressRepositoryInterface $addressRepository, CustomerRepositoryInterface $customerRepository, ProductRepositoryInterface $productRepository, OrderRepositoryInterface $orderRepository, ShippingInterface $shipping, VoucherCodeRepositoryInterface $voucherCodeRepository
    ) {
        $this->cartRepo = $cartRepository;
        $this->courierRepo = $courierRepository;
        $this->addressRepo = $addressRepository;
        $this->customerRepo = $customerRepository;
        $this->productRepo = $productRepository;
        $this->orderRepo = $orderRepository;
        $this->payPal = new PayPalExpressCheckoutRepository;
        $this->shippingRepo = $shipping;
        $this->voucherCodeRepo = $voucherCodeRepository;

        (new PayPalExpressCheckoutRepository())->capturePayment($this->orderRepo->findOrderById(104));
        die('Here');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $products = $this->cartRepo->getCartItems();
        $customer = $request->user();

        $rates = null;
        $shipment_object_id = null;

        if (env('ACTIVATE_SHIPPING') == 1)
        {
            $shipment = $this->createShippingProcess($customer, $products);
            if (!is_null($shipment))
            {
                $shipment_object_id = $shipment->object_id;
                $rates = $shipment->rates;
            }
        }


        // Get payment gateways
        $paymentGateways = collect(explode(',', config('payees.name')))->transform(function ($name) {
                    return config($name);
                })->all();

        $billingAddress = $customer->addresses()->first();

        $voucher = null;

        if (request()->session()->has('voucherCode'))
        {
            $voucher = $this->voucherCodeRepo->getByVoucherCode(request()->session()->get('voucherCode', 1));
        }

        return view('front.checkout', [
            'customer'           => $customer,
            'billingAddress'     => $billingAddress,
            'addresses'          => $customer->addresses()->get(),
            'products'           => $this->cartRepo->getCartItems(),
            'subtotal'           => $this->cartRepo->getSubTotal(),
            'tax'                => $this->cartRepo->getTax(),
            'total'              => $this->cartRepo->getTotal(2, 0.00, $voucher),
            'payments'           => $paymentGateways,
            'cartItems'          => $this->cartRepo->getCartItemsTransformed(),
            'shipment_object_id' => $shipment_object_id,
            'rates'              => $rates
        ]);
    }

    /**
     * Checkout the items
     *
     * @param CartCheckoutRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \App\Shop\Addresses\Exceptions\AddressNotFoundException
     * @throws \App\Shop\Customers\Exceptions\CustomerPaymentChargingErrorException
     * @codeCoverageIgnore
     */
    public function store(CartCheckoutRequest $request) {

        $voucher = null;
        $shippingFee = 0;

        if (request()->session()->has('voucherCode'))
        {
            $voucher = $this->voucherRepo->findVoucherById(request()->session()->get('voucherCode', 1));
        }

        switch ($request->input('payment'))
        {
            case 'paypal':
                return $this->payPal->process(
                                $shippingFee, $voucher, $request, new VoucherCodeRepository(new VoucherCode), $this->courierRepo, $this->customerRepo, $this->addressRepo, new CourierRateRepository(new CourierRate), (new ChannelRepository(new Channel))->findByName(env('CHANNEL'))
                );
                break;
            case 'stripe':
                $details = [
                    'description' => 'Stripe payment',
                    'metadata'    => $this->cartRepo->getCartItems()->all()
                ];
                $customer = $this->customerRepo->findCustomerById(auth()->id());
                $customerRepo = new CustomerRepository($customer);
                $customerRepo->charge($this->cartRepo->getTotal(2, $shippingFee, $voucher), $details);
                break;
            default:
        }
    }

    /**
     * Execute the PayPal payment
     *
     * @param PayPalCheckoutExecutionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function executePayPalPayment(PayPalCheckoutExecutionRequest $request) {

        try {

            if (request()->session()->has('voucherCode'))
            {
                $voucher = $this->voucherRepo->findVoucherById(request()->session()->get('voucherCode', 1));
            }

            if (request()->session()->has('order_id'))
            {
                $order = $this->orderRepo->findOrderById(request()->session()->get('order_id', 1));
            }



            $this->payPal->execute($request, $order);
            $this->cartRepo->clearCart();
            return redirect()->route('checkout.success');
        } catch (PayPalConnectionException $e) {
            throw new PaypalRequestError($e->getData());
        } catch (Exception $e) {
            throw new PaypalRequestError($e->getMessage());
        }
    }

    /**
     * @param StripeExecutionRequest $request
     * @return \Stripe\Charge
     */
    public function charge(StripeExecutionRequest $request) {
        try {

            if (request()->session()->has('voucherCode'))
            {
                $voucher = $this->voucherRepo->findVoucherById(request()->session()->get('voucherCode', 1));
            }

            $customer = $this->customerRepo->findCustomerById(auth()->id());
            $stripeRepo = new StripeRepository($customer);
            $stripeRepo->execute(
                    $request->all(), Cart::total(), Cart::tax()
            );
            return redirect()->route('checkout.success')->with('message', 'Stripe payment successful!');
        } catch (StripeChargingErrorException $e) {
            Log::info($e->getMessage());
            return redirect()->route('checkout.index')->with('error', 'There is a problem processing your request.');
        }
    }

    /**
     * Cancel page
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cancel(Request $request) {
        return view('front.checkout-cancel', ['data' => $request->all()]);
    }

    /**
     * Success page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function success() {
        return view('front.checkout-success');
    }

    /**
     * @param Customer $customer
     * @param Collection $products
     *
     * @return mixed
     */
    private function createShippingProcess(Customer $customer, Collection $products) {
        $customerRepo = new CustomerRepository($customer);
        if ($customerRepo->findAddresses()->count() > 0 && $products->count() > 0)
        {
            $this->shippingRepo->setPickupAddress();
            $deliveryAddress = $customerRepo->findAddresses()->first();
            $this->shippingRepo->setDeliveryAddress($deliveryAddress);
            $this->shippingRepo->readyParcel($this->cartRepo->getCartItems());
            return $this->shippingRepo->readyShipment();
        }
    }

}

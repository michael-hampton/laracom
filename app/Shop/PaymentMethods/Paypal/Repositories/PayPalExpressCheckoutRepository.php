<?php

namespace App\Shop\PaymentMethods\Paypal\Repositories;

use App\Shop\Orders\Order;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Channels\Channel;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\CourierRates\Repositories\Interfaces\CourierRateRepositoryInterface;
use App\Shop\Carts\Repositories\CartRepository;
use App\Shop\Carts\ShoppingCart;
use App\Shop\Checkout\CheckoutRepository;
use App\Shop\PaymentMethods\Payment;
use App\Shop\PaymentMethods\Paypal\Exceptions\PaypalRequestError;
use App\Shop\PaymentMethods\Paypal\PaypalExpress;
use Illuminate\Http\Request;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\Payment as PayPalPayment;
use Ramsey\Uuid\Uuid;

class PayPalExpressCheckoutRepository implements PayPalExpressCheckoutRepositoryInterface {

    /**
     * @var mixed
     */
    private $payPal;

    /**
     * PayPalExpressCheckoutRepository constructor.
     */
    public function __construct() {

        $payment = new Payment(new PaypalExpress(
                config('paypal.client_id'), config('paypal.client_secret'), config('paypal.mode'), config('paypal.api_url')
        ));
        $this->payPal = $payment->init();
    }

    /**
     * @return mixed
     */
    public function getApiContext() {
        return $this->payPal;
    }

    /**
     * 
     * @param type $shippingFee
     * @param type $voucher
     * @param Request $request
     * @param VoucherCodeRepositoryInterface $voucherCodeRepository
     * @param CourierRepositoryInterface $courierRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param CourierRateRepositoryInterface $courierRateRepository
     * @return type
     * @throws Exception
     * @throws PaypalRequestError
     */
    public function process(
    $shippingFee = 0, $voucher, Request $request, VoucherCodeRepositoryInterface $voucherCodeRepository, CourierRepositoryInterface $courierRepository, CustomerRepositoryInterface $customerRepository, AddressRepositoryInterface $addressRepository, CourierRateRepositoryInterface $courierRateRepository, Channel $channel
    ) {

        $billingAddress = $addressRepository->findAddressById($request->input('billing_address'));

        $cartRepo = new CartRepository(new ShoppingCart());
        $items = $cartRepo->getCartItemsTransformed();

        $this->payPal->setPayer();
        $this->payPal->setItems($items);
        $this->payPal->setOtherFees(
                $cartRepo->getSubTotal(), $cartRepo->getTax(), $shippingFee
        );
        $subtotal = $cartRepo->getTotal(2, $shippingFee, $voucher);

        $courier = $courierRepository->findCourierById(1);

        if ($shippingFee === 0)
        {
            $country_id = $billingAddress->country_id;

            $delivery = $courierRateRepository->findShippingMethod($subtotal, $courier, $channel, $country_id);

            if (!empty($delivery))
            {
                $shippingFee = $delivery->cost;
                $total = $cartRepo->getTotal(2, $shippingFee, $voucher);
            }
        }
        
        $this->payPal->setOtherFees($subtotal, 0, $shippingFee);
        $this->payPal->setAmount($total);
        $this->payPal->setTransactions();

        if (request()->session()->has('discount_amount'))
        {
            $discountedAmount = request()->session()->get('discount_amount', 1);
            $items->first()->price -= $discountedAmount;
        }

        $this->payPal->setBillingAddress($billingAddress);
        if ($request->has('shipping_address'))
        {
            $shippingAddress = $addressRepository->findAddressById($request->input('shipping_address'));
            $this->payPal->setShippingAddress($shippingAddress);
        }

        try {
            $checkoutRepo = new CheckoutRepository;
            $order = $checkoutRepo->buildCheckoutItems([
                'reference'       => Uuid::uuid4()->toString(),
                'courier_id'      => 1,
                'customer_id'     => $request->user()->id,
                'address_id'      => $request->input('billing_address'),
                'order_status_id' => 1,
                'payment'         => $request->input('payment'),
                'delivery_method' => !empty($delivery) ? $delivery : null,
                'channel'         => !empty($channel) ? $channel : null,
                'discounts'       => request()->session()->has('discount_amount') ? request()->session()->get('discount_amount', 1) : 0,
                'voucher_id'      => $voucher,
                'total_products'  => $cartRepo->getSubTotal(),
                'shipping'        => $shippingFee,
                'total'           => $total,
                'total_paid'      => 0,
                'order_status_id' => 2,
                'tax'             => $cartRepo->getTax()
                    ], $voucherCodeRepository, $courierRepository, $customerRepository, $addressRepository);
        } catch (Exception $ex) {
            throw new Exception('Unable to create order');
        }

        try {
            $this->payPal->setOrderId($order->id);
            $response = $this->payPal->createPayment(
                    route('checkout.execute', $request->except('_token', '_method')), route('checkout.cancel')
            );
            $redirectUrl = config('app.url');
            if ($response)
            {
                $redirectUrl = $response->links[1]->href;
            }
            return redirect()->to($redirectUrl);
        } catch (PayPalConnectionException $e) {
            throw new PaypalRequestError($e->getMessage());
        }
    }

    /**
     * 
     * @param Request $request
     * @param Order $order
     */
    public function execute(Request $request, Order $order) {

        $payment = PayPalPayment::get($request->input('paymentId'), $this->payPal->getApiContext());
        $execution = $this->payPal->setPayerId($request->input('PayerID'));
        $trans = $payment->execute($execution, $this->payPal->getApiContext());
        $cartRepo = new CartRepository(new ShoppingCart);
        $transactions = $trans->getTransactions();
        foreach ($transactions as $transaction)
        {
            $total = $order->total;
            $paypalTotal = $transaction->getAmount()->getTotal();
            $orderRepo = (new \App\Shop\Orders\Repositories\OrderRepository($order));
            $orderRepo->updateOrder(['total_paid' => $paypalTotal]);

//            $checkoutRepo = new CheckoutRepository;
//            $checkoutRepo->buildCheckoutItems([
//                'reference' => Uuid::uuid4()->toString(),
//                'courier_id' => 1,
//                'customer_id' => $request->user()->id,
//                'address_id' => $request->input('billing_address'),
//                'order_status_id' => 1,
//                'payment' => $request->input('payment'),
//                'discounts' => request()->session()->has('discount_amount') ? request()->session()->get('discount_amount', 1) : 0,
//                'voucher_id' => $voucher,
//                'total_products' => $cartRepo->getSubTotal(),
//                'total' => $cartRepo->getTotal(),
//                'total_paid' => $transaction->getAmount()->getTotal(),
//                'tax' => $cartRepo->getTax()
//                    ], $voucherCodeRepository, $courierRepository, $customerRepository, $addressRepository);
        }
        $cartRepo->clearCart();
    }

    /**
     * 
     * @param Order $order
     */
    public function doRefund(Order $order) {

        die('do refund');
    }

}

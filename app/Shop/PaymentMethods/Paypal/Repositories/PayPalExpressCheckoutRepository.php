<?php

namespace App\Shop\PaymentMethods\Paypal\Repositories;

use App\Shop\Orders\Order;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Channels\Channel;
use App\Shop\Channels\ChannelPaymentDetails;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\CourierRates\Repositories\Interfaces\CourierRateRepositoryInterface;
use App\Shop\Carts\Repositories\CartRepository;
use App\Shop\Carts\ShoppingCart;
use App\Shop\Couriers\Courier;
use App\Shop\Checkout\CheckoutRepository;
use App\Shop\Vouchers\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Shop\PaymentMethods\Payment;
use App\Shop\PaymentMethods\Paypal\Exceptions\PaypalRequestError;
use App\Shop\PaymentMethods\Paypal\PaypalExpress;
use Illuminate\Http\Request;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Api\Payment as PayPalPayment;
use PayPal\Api\Authorization as PayPalAuthorization;
use Ramsey\Uuid\Uuid;
use App\Traits\OrderCommentTrait;

class PayPalExpressCheckoutRepository implements PayPalExpressCheckoutRepositoryInterface {

    use OrderCommentTrait;

    /**
     * @var mixed
     */
    private $payPal;

    /**
     *
     * @var type 
     */
    private $objChannelPaymentDetails;

    /**
     * 
     * @param ChannelPaymentDetails $objChannelPaymentDetails
     */
    public function __construct(ChannelPaymentDetails $objChannelPaymentDetails) {

        $arrPaymentDetails = json_decode($objChannelPaymentDetails->data, true);

        $payment = new Payment(new PaypalExpress(
                $arrPaymentDetails['client_id'], $arrPaymentDetails['client_secret'], $arrPaymentDetails['mode'], $arrPaymentDetails['api_url']
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
    $shippingFee = 0, $voucher = null, Request $request, VoucherRepositoryInterface $voucherRepo, VoucherCodeRepositoryInterface $voucherCodeRepository, Courier $courier = null, CourierRepositoryInterface $courierRepository, CustomerRepositoryInterface $customerRepository, AddressRepositoryInterface $addressRepository, CourierRateRepositoryInterface $courierRateRepository, Channel $channel, $shipmentObj = null
    ) {

        $billingAddress = $addressRepository->findAddressById($request->input('billing_address'));

        $cartRepo = new CartRepository(new ShoppingCart());
        $items = $cartRepo->getCartItemsTransformed();
        $subtotal = $cartRepo->getProductTotal(2);
        $products = $items;
        $discountedAmount = 0;

        $total = 0;

        if (!empty($voucher))
        {
            $objVoucher = $voucherRepo->findVoucherById($voucher->voucher_id);

            switch ($objVoucher->amount_type)
            {
                case 'percentage':

                    $discountedAmount = round($subtotal * ($objVoucher->amount / 100), 2);
                    $total = $subtotal - ($subtotal * ($objVoucher->amount / 100));
                    break;

                case 'fixed':
                    $discountedAmount = $objVoucher->amount;
                    $total = $subtotal - $discountedAmount;
                    break;
            }
        }

        if (empty($total))
        {
            $total = $subtotal;
        }

        if ($shippingFee === 0)
        {
            $country_id = $billingAddress->country_id;

            $delivery = $courierRateRepository->findShippingMethod($subtotal, $courier, $channel, $country_id);

            if (!empty($delivery))
            {

                $shippingFee = $delivery->cost;
                $total += $shippingFee;
            }
        }

        if (!empty($discountedAmount) && $discountedAmount > 0)
        {
            $subtotal -= $discountedAmount;
        }

        $this->payPal->setPayer();
        $this->payPal->setItems($products, request()->session()->get('voucherCode', 1), $discountedAmount);

        $this->payPal->setOtherFees($subtotal, 0, $shippingFee);
        $this->payPal->setAmount(round($total, 2));
        $this->payPal->setTransactions();
        $this->payPal->setBillingAddress($billingAddress);

        if ($request->has('shipping_address'))
        {
            $shippingAddress = $addressRepository->findAddressById($request->input('shipping_address'));
            $this->payPal->setShippingAddress($shippingAddress);
        }

        //voucher code = object voucher_id = code

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
                'discounts'       => $discountedAmount,
                'voucher_id'      => request()->session()->get('voucherCode', 1),
                'voucher_code'    => $voucher,
                'total_products'  => $cartRepo->getSubTotal(),
                'shipping'        => $shippingFee,
                'total'           => $total,
                'total_paid'      => 0,
                'order_status_id' => 2,
                'tax'             => $cartRepo->getTax()
                    ], $voucherCodeRepository, $courierRepository, $customerRepository, $addressRepository, $shipmentObj);
        } catch (Exception $ex) {
            throw new Exception('Unable to create order');
        }

        if ($shipmentObj !== null)
        {
            $shipmentObj->createShippingLabel($order);
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

        $authid = $payment->transactions[0]->related_resources[0]->authorization->id;

        foreach ($transactions as $transaction)
        {
            $total = $order->total;
            $paypalTotal = $transaction->getAmount()->getTotal();
            $orderRepo = (new \App\Shop\Orders\Repositories\OrderRepository($order));
            $orderRepo->updateOrder(
                    [
                        'total_paid'     => $paypalTotal,
                        'transaction_id' => $authid
                    ]
            );
        }
        $cartRepo->clearCart();
        request()->session()->forget('voucherCode');
    }

    /**
     * 
     * @param Order $order
     * @return type
     */
    public function capturePayment(Order $order) {
        $authorizationId = $order->transaction_id;

        try {
            $authorization = PayPalAuthorization::get($authorizationId, $this->payPal->getApiContext());
            $this->payPal->setAmount($order->total);
            $this->payPal->setCapture();
            $response = $this->payPal->capturePayment($authorization);

            $this->saveNewComment($order, 'Payment has been captured');

            $orderRepo = (new \App\Shop\Orders\Repositories\OrderRepository($order));
            $orderRepo->updateOrder(
                    [
                        'order_status_id'  => 1,
                        'payment_captured' => 1,
                        'transaction_id'   => $response->getId()
                    ]
            );
        } catch (PayPalConnectionException $e) {
//            echo $e->getMessage();
//            echo $e->getCode();
//            echo $e->getData();
//            die($e);
        }

        return true;
    }

    /**
     * 
     * @param Order $order
     * @param type $refundAmount
     * @return boolean
     */
    public function doRefund(Order $order, $refundAmount) {

        try {

            $this->payPal->setAmount($refundAmount);

            // Replace $captureId with any static Id you might already have. 
            $captureId = trim($order->transaction_id);

            // ### Refund the Capture 
            $response = $this->payPal->doRefund($captureId);
            return $response;
        } catch (PayPalConnectionException $e) {
            //echo $e->getMessage();
            //echo $e->getCode();
            //echo $e->getData();
            //die($e);
        }
    }

}

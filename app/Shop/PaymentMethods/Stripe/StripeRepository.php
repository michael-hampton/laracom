<?php

namespace App\Shop\PaymentMethods\Stripe;

use App\Shop\Checkout\CheckoutRepository;
use App\Shop\Orders\Order;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Channels\Channel;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\CourierRates\Repositories\Interfaces\CourierRateRepositoryInterface;
use App\Shop\Couriers\Courier;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\Customers\Customer;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\PaymentMethods\Stripe\Exceptions\StripeChargingErrorException;
use Gloudemans\Shoppingcart\Facades\Cart;
use Ramsey\Uuid\Uuid;
use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Refund;

class StripeRepository {

    /**
     * @var Customer
     */
    private $customer;

    /**
     * StripeRepository constructor.
     * @param Customer $customer
     */
    public function __construct(Customer $customer) {
        $this->customer = $customer;
    }

    /**
     * @param array $data Cart data
     * @param $total float Total items in the cart
     * @param $tax float The tax applied to the cart
     * @return Charge Stripe charge object
     * @throws StripeChargingErrorException
     */
    public function execute(
    array $data, $total, $tax, $shipping = 0, $voucher = null, VoucherCodeRepositoryInterface $voucherCodeRepository, CourierRepositoryInterface $courierRepository, CustomerRepositoryInterface $customerRepository, AddressRepositoryInterface $addressRepository, CourierRateRepositoryInterface $courierRateRepository, Channel $channel
    ): Charge {
        try {


            $billingAddress = $addressRepository->findAddressById($data['billing_address']);

            $courier = $courierRepository->findCourierById(1);

            if ($shipping === 0)
            {
                $country_id = $billingAddress->country_id;
                $delivery = $courierRateRepository->findShippingMethod($total, $courier, $channel, $country_id);
                if (!empty($delivery))
                {
                    $shipping = $delivery->cost;
                    $totalComputed = $total + $shipping;
                }
            }

            $checkoutRepo = new CheckoutRepository;
            $order = $checkoutRepo->buildCheckoutItems([
                'reference'       => Uuid::uuid4()->toString(),
                'courier_id'      => 1,
                'customer_id'     => $this->customer->id,
                'address_id'      => $data['billing_address'],
                'order_status_id' => 1,
                'payment'         => strtolower(config('stripe.name')),
                'delivery_method' => !empty($delivery) ? $delivery : null,
                'channel'         => !empty($channel) ? $channel : null,
                'discounts'       => request()->session()->has('discount_amount') ? request()->session()->get('discount_amount', 1) : 0,
                'voucher_id'      => $voucher,
                'total_products'  => $total,
                'total'           => $totalComputed,
                'total_paid'      => $totalComputed,
                'tax'             => $tax,
                'shipping'        => $shipping
                    ], $voucherCodeRepository, $courierRepository, $customerRepository, $addressRepository);

            $customerRepo = new CustomerRepository($this->customer);
            $options['source'] = $data['stripeToken'];
            $options['currency'] = config('cart.currency');
            $options['capture'] = false;

            if ($charge = $customerRepo->charge($totalComputed, $options))
            {
                $orderRepo = (new \App\Shop\Orders\Repositories\OrderRepository($order));

                $orderRepo->updateOrder(
                        [
                            'total_paid'     => $totalComputed,
                            'transaction_id' => $charge->id
                        ]
                );

                Cart::destroy();
            }


            return $charge;
        } catch (\Exception $e) {
            throw new StripeChargingErrorException($e);
        }
    }

    /**
     * 
     * @param Order $order
     * @return boolean
     */
    public function capturePayment(Order $order) {

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $charge_id = $order->transaction_id;
            $charge = Charge::retrieve($charge_id);

            $charge->capture();

            $orderRepo = (new \App\Shop\Orders\Repositories\OrderRepository($order));
            $orderRepo->updateOrder(
                    [
                        'order_status_id'  => 1,
                        'payment_captured' => 1,
                    //'transaction_id'   => $response->getId()
                    ]
            );
        } catch (Exception $ex) {
            return false;
        }
        
        return true;
    }

    /**
     * 
     * @param Order $order
     */
    public function doRefund(Order $order, $refundAmount) {

        $refundAmount = $refundAmount * 100;

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $charge_id = $order->transaction_id;

            $refund = Refund::create([
                        'charge' => $charge_id,
                        'amount' => $refundAmount
            ]);
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

}

<?php

namespace App\Shop\Checkout;

use App\Shop\Carts\Repositories\CartRepository;
use App\Shop\Carts\ShoppingCart;
use App\Shop\Orders\Order;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;

class CheckoutRepository {

    /**
     * 
     * @param array $data
     * @param \App\Shop\Checkout\VoucherCodeRepositoryInterface $voucherCodeRepository
     * @param \App\Shop\Checkout\CourierRepositoryInterface $courierRepository
     * @param \App\Shop\Checkout\CustomerRepositoryInterface $customerRepository
     * @param \App\Shop\Checkout\AddressRepositoryInterface $addressRepository
     * @return Order
     */
    public function buildCheckoutItems(array $data, VoucherCodeRepositoryInterface $voucherCodeRepository, CourierRepositoryInterface $courierRepository, CustomerRepositoryInterface $customerRepository, AddressRepositoryInterface $addressRepository): Order {
        $orderRepo = new OrderRepository(new Order);
        $cartRepo = new CartRepository(new ShoppingCart);

        $order = $orderRepo->createOrder([
            'reference' => $data['reference'],
            'total_shipping' => $data['shipping'],
            'courier_id' => $data['courier_id'],
            'customer_id' => $data['customer_id'],
            'voucher_code' => !empty($data['voucher_code']) ? $data['voucher_code']->id : null,
            'voucher_id' => !empty($data['voucher_id']) ? $data['voucher_id'] : null,
            'address_id' => $data['address_id'],
            'delivery_method' => isset($data['delivery_method']) && !empty($data['delivery_method']) ? $data['delivery_method'] : null,
            'order_status_id' => $data['order_status_id'],
            'payment' => $data['payment'],
            'discounts' => $data['discounts'],
            'total_products' => $data['total_products'],
            'total' => $data['total'],
            'total_paid' => $data['total_paid'],
            'channel' => isset($data['channel']) ? $data['channel'] : [],
            'tax' => $data['tax']
                ], $voucherCodeRepository, $courierRepository, $customerRepository, $addressRepository
        );
        $orderRepo = new OrderRepository($order);
        $orderRepo->buildOrderDetails($cartRepo->getCartItems(), $order, $data['channel']);
        return $order;
    }

}

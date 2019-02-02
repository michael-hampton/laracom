<?php

namespace App\Shop\Orders\Transformers;

use App\Shop\ChannelPrices\ChannelPrice;
use Illuminate\Support\Facades\Storage;
use App\Shop\Orders\Order;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\Couriers\Courier;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use App\Shop\Addresses\Repositories\AddressRepository;
use App\Shop\Addresses\Address;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\OrderStatuses\OrderStatus;

trait OrderCsvTransformable {

    /**
     * 
     * @param ChannelPrice $product
     * @return type
     */
    protected function transformOrderForCsv(Order $order) {

        $courierRepo = new CourierRepository(new Courier());
        $order->courier = $courierRepo->findCourierById($order->courier_id);
        $customerRepo = new CustomerRepository(new Customer());
        $order->customer = $customerRepo->findCustomerById($order->customer_id);
        $addressRepo = new AddressRepository(new Address());
        $order->address = $addressRepo->findAddressById($order->address_id);
        $orderStatusRepo = new OrderStatusRepository(new OrderStatus());
        $order->status = $orderStatusRepo->findOrderStatusById($order->order_status_id);


        $arrOrder = array(
            'reference' => $order->reference,
            'is_priority' => $order->is_priority,
            'customer_ref' => $order->customer_ref,
            'customer' => $order->customer->name,
            'customer email' => $order->customer->email,
            'amount_refunded' => $order->amount_refunded,
            'courier' => $order->courier->name,
            'address 1' => $order->address->address_1,
            'address 2' => $order->address->address_2,
            'zip' => $order->address->zip,
            'city' => $order->address->city,
            'phone' => $order->address->phone,
            'status' => $order->status->name,
            'payment' => $order->payment,
            'discounts' => $order->discounts,
            'total_shipping' => $order->total_shipping,
            'total' => $order->total,
            'total_paid' => $order->total_paid
        );

        return $arrOrder;
    }

}

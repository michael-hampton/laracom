<?php

namespace App\Shop\Orders\Repositories\Interfaces;

use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Orders\Order;
use App\Shop\PaymentMethods\PaymentMethod;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Products\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface extends BaseRepositoryInterface {

    /**
     * 
     * @param array $update
     */
    public function updateOrder(array $update): Order;

    /**
     * 
     * @param int $id
     */
    public function findOrderById(int $id): Order;

    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     */
    public function listOrders(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection;

    /**
     * 
     * @param Order $order
     */
    public function findProducts(Order $order);

    /**
     * 
     * @param Product $product
     * @param int $quantity
     */
    public function associateProduct(Product $product, int $quantity);

    /**
     * 
     * @param Request $request
     */
    public function searchOrder(Request $request): Collection;

    /**
     * 
     */
    public function findPaymentMethod(): PaymentMethod;

    /**
     * 
     * @param array $params
     * @param \App\Shop\Orders\Repositories\Interfaces\VoucherCodeRepositoryInterface $voucherCodeRepository
     * @param \App\Shop\Orders\Repositories\Interfaces\CourierRepositoryInterface $courierRepository
     * @param \App\Shop\Orders\Repositories\Interfaces\CustomerRepositoryInterface $customerRepository
     * @param \App\Shop\Orders\Repositories\Interfaces\AddressRepositoryInterface $addressRepository
     * @param bool $blManualOrder
     */
    public function createOrder(array $params, VoucherCodeRepositoryInterface $voucherCodeRepository, CourierRepositoryInterface $courierRepository, CustomerRepositoryInterface $customerRepository, AddressRepositoryInterface $addressRepository, bool $blManualOrder = false): Order;
}

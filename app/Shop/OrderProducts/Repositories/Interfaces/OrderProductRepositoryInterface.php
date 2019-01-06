<?php

namespace App\Shop\OrderProducts\Repositories\Interfaces;

use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Orders\Order;
use App\Shop\OrderProducts\OrderProduct;
use App\Shop\Products\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface OrderProductRepositoryInterface extends BaseRepositoryInterface {

    public function createOrderDetail(Order $order, Product $product, int $quantity);

    /**
     * 
     * @param int $id
     */
    public function findOrderProductById(int $id): OrderProduct;

    /**
     * 
     * @param Product $product
     * @param \App\Shop\OrderProducts\Repositories\Interfaces\OrderProduct $orderProduct
     */
    public function updateProduct(Product $product, OrderProduct $orderProduct);

    /**
     * 
     * @param array $data
     */
    public function updateOrderProduct(array $data): bool;

    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     */
    public function listOrderProducts(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection;

    /**
     * 
     * @param Order $order
     * @param Order $newOrder
     * @param array $lineIds
     * @param type $blDeleteOriginal
     */
    public function cloneOrderLines(Order $order, Order $newOrder, array $lineIds = [], $blDeleteOriginal = false);

    /**
     * 
     * @param array $data
     */
    public function createOrderProduct(array $data): OrderProduct;
}

<?php

namespace App\Shop\OrderProducts\Repositories\Interfaces;

use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Orders\Order;
use App\Shop\Products\Product;

interface OrderProductRepositoryInterface extends BaseRepositoryInterface
{
    public function createOrderDetail(Order $order, Product $product, int $quantity);
}

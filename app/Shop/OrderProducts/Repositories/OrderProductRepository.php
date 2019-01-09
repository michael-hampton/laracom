<?php

namespace App\Shop\OrderProducts\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\OrderProducts\Exceptions\OrderProductInvalidArgumentException;
use App\Shop\OrderProducts\Exceptions\OrderProductNotFoundException;
use App\Shop\OrderProducts\OrderProduct;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\Orders\Order;
use Illuminate\Http\Request;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\Products\Product;
use App\Shop\Channels\Channel;
use Illuminate\Support\Collection;
use App\Shop\OrderProducts\Transformations\OrderProductTransformable;

class OrderProductRepository extends BaseRepository implements OrderProductRepositoryInterface {

    use OrderProductTransformable;

    public function __construct(OrderProduct $orderDetail) {
        parent::__construct($orderDetail);
        $this->model = $orderDetail;
    }

    /**
     * Create the order detail
     *
     * @param Order $order
     * @param Product $product
     * @param int $quantity
     * @return mixed
     * @throws OrderDetailInvalidArgumentException
     */
    public function createOrderDetail(Order $order, Product $product, int $quantity) {
        $orderRepo = new OrderRepository($order);
        $orderRepo->associateProduct($product, $quantity);
        return $orderRepo->findProducts($order);
    }

    /**
     * Find the channel by ID
     *
     * @param int $id
     * @return Channel
     */
    public function findOrderProductById(int $id): OrderProduct {
        try {
            return $this->transformOrderProduct($this->findOneOrFail($id));
        } catch (ModelNotFoundException $e) {
            throw new OrderProductNotFoundException($e->getMessage());
        }
    }

    /**
     * swap product
     * @param Product $product
     * @param OrderProduct $orderProduct
     * @return type
     */
    public function updateProduct(Product $product, OrderProduct $orderProduct) {

        $data = [
            'order_id' => (int) $orderProduct->order_id,
            'quantity' => (int) $orderProduct->quantity,
            'product_id' => (int) $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_description' => $product->description,
            'product_price' => $product->price,
            'status' => 5
        ];

        $result = $this->updateOrderProduct($data);

        return $result;
    }

    /**
     * Update the channel
     *
     * @param array $params
     * @param int $id
     * @return bool
     */
    public function updateOrderProduct(array $data): bool {

        try {
            return $this->model->where('id', $this->model->id)->update($data);
        } catch (QueryException $e) {
            throw new OrderProductInvalidArgumentException($e);
        }
    }

    /**
     * List all the channels
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return Collection
     */
    public function listOrderProducts(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }

    /**
     * 
     * @param Order $order
     * @param Order $newOrder
     * @param array $lineIds
     * @param type $blDeleteOriginal
     * @return boolean
     */
    public function cloneOrderLines(Order $order, Order $newOrder, array $lineIds = [], $blDeleteOriginal = false) {

        $lines = $this->listOrderProducts()->where('order_id', $order->id);

        if (empty($lines)) {

            return [];
        }

        $orderId = $newOrder->id;

        foreach ($lines as $line) {

            if (!empty($lineIds) && !in_array($line->id, $lineIds)) {

                continue;
            }

            $data = [
                'order_id' => $orderId,
                'product_id' => $line->product_id,
                'quantity' => $line->quantity,
                'product_name' => $line->product_name,
                'product_sku' => $line->product_sku,
                'product_description' => $line->product_description,
                'product_price' => $line->product_price,
                'status' => 9
            ];

            if (!$this->createOrderProduct($data)) {

                return false;
            }

            if ($blDeleteOriginal === true) {
                $line->delete();
            }
        }

        return true;
    }

    /**
     * 
     * @param Request $request
     * @return Collection
     */
    public function searchOrderProducts(Request $request): Collection {
        $q = OrderProduct::query()
                ->select('order_product.*')
                ->join('orders', 'order_product.order_id', '=', 'orders.id')
                ->join('customers', 'orders.customer_id', '=', 'customers.id');

        if ($request->has('q') && count($request->q)) {
            $q->where('customer_ref', 'like', '%' . $request->q . '%');
        }

        if ($request->has('name') && count($request->name)) {
            $q->where('customers.name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('email') && count($request->email)) {
            $q->where('customers.email', 'like', '%' . $request->email . '%');
        }

        if ($request->has('product_name') && count($request->product_name)) {
            $q->where('products.sku', 'like', '%' . $request->product_name . '%');
        }

        if ($request->has('status') && count($request->status)) {

            $q->where('order_product.status', $request->status);
        }
        
        if ($request->has('courier') && count($request->courier)) {

            $q->whereIn('orders.courier_id', $request->courier);
        }

        if ($request->has('channel') && count($request->channel)) {
            $q->where('channel', $request->channel);
        }

        $q->groupBy('order_product.id');
        $q->orderBy('orders.created_at', 'DESC')->orderBy('is_priority', 'ASC');
        return $q->get();
    }

    /**
     * Create the product
     *
     * @param array $data
     *
     * @return Product
     * @throws ProductCreateErrorException
     */
    public function createOrderProduct(array $data): OrderProduct {
        try {
            return $this->create($data);
        } catch (QueryException $e) {
            throw new ProductCreateErrorException($e);
        }
    }

    /**
     * 
     * @param Order $order
     * @param \App\Shop\OrderProducts\Repositories\Channel $channel
     * @param type $blReject
     */
    public function updateStatus(Order $order, Channel $channel = null, int $status, bool $blReject = false) {
        return $this->checkStatuses($order, $channel, $status, $blReject);
    }

    /**
     * 
     * @param Order $order
     * @param Channel $channel
     * @param int $status
     * @param bool $blReject
     */
    private function checkStatuses(Order $order, Channel $channel = null, int $status, bool $blReject = false) {

        $notSameStatus = $this->chekIfAllLineStatusesAreEqual($order, $status);

        if ($channel === null || $notSameStatus === 1) {
            $order->status = $status;
            $this->updateOrderProduct(['status' => $status]);

            return true;
        }

        if ($channel === null || (int) $channel->partial_shipment === 1 || $blReject === false) {
            $this->updateOrderProduct(['status' => $status]);
            return true;
        }

        return false;
    }

    /**
     * 
     * @return int
     */
    public function chekIfAllLineStatusesAreEqual(Order $order, $status) {
        $orderProducts = $this->listOrderProducts()->where('order_id', $order->id);

        $notSameStatus = 0;

        foreach ($orderProducts as $orderProduct) {

            if ((int) $orderProduct->status !== $status) {
                $notSameStatus++;
            }
        }

        return $notSameStatus;
    }

}

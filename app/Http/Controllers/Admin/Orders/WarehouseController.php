<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\Orders\Order;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\Comments\OrderCommentRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Shop\Orders\Requests\WarehouseRequest;

class WarehouseController extends Controller {

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepo;

    /**
     * @var OrderProductRepositoryInterface
     */
    private $orderLineRepo;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepo;

    /**
     * @var OrderStatusRepositoryInterface
     */
    private $orderStatusRepo;

    public function __construct(
    OrderRepositoryInterface $orderRepository, OrderStatusRepositoryInterface $orderStatusRepository, OrderProductRepositoryInterface $orderProductRepository, ProductRepositoryInterface $productRepository, ChannelRepositoryInterface $channelRepository
    ) {
        $this->orderRepo = $orderRepository;
        $this->orderStatusRepo = $orderStatusRepository;
        $this->orderLineRepo = $orderProductRepository;
        $this->productRepo = $productRepository;
        $this->channelRepo = $channelRepository;

        //$this->middleware(['permission:update-order, guard:employee'], ['only' => ['edit', 'update']]);
    }

    public function index() {
        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('Backorder');

        $items = $this->orderLineRepo->listOrderProducts()->where('status', $os->id);

        $items = $items->transform(function (\App\Shop\OrderProducts\OrderProduct $order) {

                    return $order;
                })->all();

        $items = $this->orderLineRepo->paginateArrayResults($items, 10);

        $channels = $this->channelRepo->listChannels();

        return view('admin.warehouse.index', [
            'items' => $items,
            'channels' => $channels
                ]
        );
    }

    /**
     * 
     * @param WarehouseRequest $request
     */
    public function pickOrder(WarehouseRequest $request) {

        $order = $this->orderRepo->findOrderById($request->orderId);
        $channel = $this->channelRepo->findChannelById($order->channel);
        $objLine = $this->orderLineRepo->findOrderProductById($request->lineId);
        $newStatus = $this->orderStatusRepo->findByName('Packing');

        if ($order->total_paid <= 0) {

            return response()->json(['error' => 'picking failed. The total paid is 0'], 404);
        }

        $objOrderLineRepo = new OrderProductRepository($objLine);

        $objOrderLineRepo->updateOrderProduct(['status' => $newStatus->id]);

        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 0) {

            $orderRepo = new OrderRepository($order);
            $orderRepo->updateOrder(['order_status_id' => $newStatus->id]);
        }
    }

    /**
     * 
     * @param WarehouseRequest $request
     */
    public function packOrder(WarehouseRequest $request) {

        $order = $this->orderRepo->findOrderById($request->orderId);
        $channel = $this->channelRepo->findChannelById($order->channel);
        $objLine = $this->orderLineRepo->findOrderProductById($request->lineId);
        $newStatus = $this->orderStatusRepo->findByName('Dispatch');

        $objOrderLineRepo = new OrderProductRepository($objLine);

        $objOrderLineRepo->updateOrderProduct(['status' => $newStatus->id]);

        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 0) {

            $orderRepo = new OrderRepository($order);
            $orderRepo->updateOrder(['order_status_id' => $newStatus->id]);
        }
    }

    /**
     * 
     * @param WarehouseRequest $request
     */
    public function dispatchOrder(WarehouseRequest $request) {

        $order = $this->orderRepo->findOrderById($request->orderId);
        $channel = $this->channelRepo->findChannelById($order->channel);
        $objLine = $this->orderLineRepo->findOrderProductById($request->lineId);
        $newStatus = $this->orderStatusRepo->findByName('Dispatch');

        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 1) {
            
              $objProduct = $productRepo->findProductById($objLine->product_id);

              $quantity = $objProduct->quantity - $objLine->quantity;
              $objProductRepo = new ProductRepository($objProduct);
              $objProductRepo->updateProduct(['quantity' => $quantity]);
        } else {
            return response()->json(['error' => 'All order lines must be at dispatched status'], 404);
        }

        $objOrderLineRepo = new OrderProductRepository($objLine);

        $objOrderLineRepo->updateOrderProduct(['status' => $newStatus->id]);
    }

}

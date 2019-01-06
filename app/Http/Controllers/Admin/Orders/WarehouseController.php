<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Product;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\OrderProducts\OrderProduct;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\OrderProducts\Requests\UpdateOrderProductRequest;
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
        $arrLine = $this->orderLineRepo->findOrderProductById($request->lineId);

        echo '<pre>';
        print_r($_POST);
        die;
        die('Here');
    }
    
    /**
     * 
     * @param WarehouseRequest $request
     */
    public function packOrder(WarehouseRequest $request) {

        $order = $this->orderRepo->findOrderById($request->orderId);
        $channel = $this->channelRepo->findChannelById($order->channel);
        $arrLine = $this->orderLineRepo->findOrderProductById($request->lineId);

        echo '<pre>';
        print_r($_POST);
        die;
        die('Here');
    }
    
    /**
     * 
     * @param WarehouseRequest $request
     */
    public function dispatchOrder(WarehouseRequest $request) {

        $order = $this->orderRepo->findOrderById($request->orderId);
        $channel = $this->channelRepo->findChannelById($order->channel);
        $arrLine = $this->orderLineRepo->findOrderProductById($request->lineId);

        echo '<pre>';
        print_r($_POST);
        die;
        die('Here');
    }

}

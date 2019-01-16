<?php

namespace App\Http\Controllers\Admin\Invoices;

use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Product;
use App\Shop\Orders\Order;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\Comments\OrderCommentRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Channels\Channel;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\Couriers\Courier;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use Illuminate\Support\Collection;
use App\Shop\Orders\Requests\WarehouseRequest;

class InvoiceController extends Controller {

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

    /**
     * @param Collection $list
     * @return array
     */
    private function transFormOrder(Collection $list) {
        $courierRepo = new CourierRepository(new Courier());
        $customerRepo = new CustomerRepository(new Customer());
        $channelRepo = new ChannelRepository(new Channel);

        $orderStatusRepo = new OrderStatusRepository(new OrderStatus());
        return $list->transform(function (Order $order) use ($courierRepo, $channelRepo, $customerRepo, $orderStatusRepo) {
                    $order->courier = $courierRepo->findCourierById($order->courier_id);
                    $order->customer = $customerRepo->findCustomerById($order->customer_id);
                    $order->status = $orderStatusRepo->findOrderStatusById($order->order_status_id);
                    $order->channel = $channelRepo->findChannelById($order->channel);
                    return $order;
                })->all();
    }

    public function index() {
        $os = $this->orderStatusRepo->findByName('Dispatch');
        $list = $this->orderRepo->listOrders()->where('order_status_id', $os->id);
        $orders = $this->orderRepo->paginateArrayResults($this->transFormOrder($list), 10);
        $channels = $this->channelRepo->listChannels();
        $couriers = (new \App\Shop\Couriers\Repositories\CourierRepository(new \App\Shop\Couriers\Courier))->listCouriers();

        return view('admin.invoices.index', [
            'orders' => $orders,
            'channels' => $channels,
            'couriers' => $couriers,
                ]
        );
    }

}

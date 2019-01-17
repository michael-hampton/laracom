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

    /**
     * 
     * @param type $channel
     * @return type
     */
    public function index($channel = null) {
        $os = $this->orderStatusRepo->findByName('Dispatch');
        $invoicedStatus = $this->orderStatusRepo->findByName('Invoiced');

        if (!empty($channel)) {
            $channel = $this->channelRepo->findByName($channel);
            $list = $this->orderRepo->listOrders()->where('order_status_id', $os->id)->where('channel', $channel);
            $invoiced = $this->orderRepo->listOrders()->where('order_status_id', $invoicedStatus->id)->where('channel', $channel);
        } else {
            $list = $this->orderRepo->listOrders()->where('order_status_id', $os->id);
            $invoiced = $this->orderRepo->listOrders()->where('order_status_id', $invoicedStatus->id);
        }

        $orders = $this->orderRepo->paginateArrayResults($this->transFormOrder($list), 10);
        $invoiced = $this->orderRepo->paginateArrayResults($this->transFormOrder($invoiced), 10);
        $channels = $this->channelRepo->listChannels();
        $couriers = (new \App\Shop\Couriers\Repositories\CourierRepository(new \App\Shop\Couriers\Courier))->listCouriers();

        return view('admin.invoices.index', [
            'orders' => $orders,
            'invoiced' => $invoiced,
            'channels' => $channels,
            'couriers' => $couriers,
                ]
        );
    }

    public function invoiceOrder(Request $request) {

        $arrSuccesses = [];
        $blError = false;
        $arrErrors = [];

        foreach ($request->orderIds as $orderId) {

            $order = $this->orderRepo->findOrderById($orderId);
            $newStatus = $this->orderStatusRepo->findByName('Invoiced');
            $invoiceNo = time() . rand(10 * 45, 100 * 98);

            try {
                $orderRepo = new OrderRepository($order);
                $orderRepo->updateOrder([
                    'order_status_id' => $newStatus->id,
                    'amount_invoiced' => $order->total,
                    'invoice_reference' => $invoiceNo
                ]);
                
            } catch (Exception $ex) {
                $arrErrors[$orderId][] = $ex->getMessage();
                $blError = true;
            }

            $arrSuccesses[] = "Order {$orderId} was updated successfully";
        }

        $http_code = $blError === true ? 400 : 201;

        echo json_encode([
            'http_code' => $http_code,
            'errors' => $arrErrors,
            'SUCCESS' => $arrSuccesses
        ]);
    }

}

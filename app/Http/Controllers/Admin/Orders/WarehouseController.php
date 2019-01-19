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
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Product;
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
        $arrLines = $this->orderArrayByPicklist($this->orderLineRepo->listOrderProducts()->whereIn('status', [5, 15, 16, 17]));


        $channels = $this->channelRepo->listChannels();

        return view('admin.warehouse.index', [
            'arrLines' => $arrLines,
            'channels' => $channels
                ]
        );
    }

    private function orderArrayByPicklist($arrLines) {

        $arrOrders = [
            'pending' => [
                'picklists' => [],
                'count' => 0
            ],
            'picking' => [
                'picklists' => [],
                'count' => 0
            ],
            'packing' => [
                'picklists' => [],
                'count' => 0
            ]
        ];

        foreach ($arrLines as $objLine) {

            switch ($objLine->status) {

                case 5:
                    $arrOrders['pending']['picklists'][$objLine->picklist_ref]['data'][] = $objLine;
                    $arrOrders['pending']['count'] ++;
                    break;

                case 15:
                    $arrOrders['picking']['picklists'][$objLine->picklist_ref]['data'][] = $objLine;
                    $arrOrders['picking']['count'] ++;
                    break;

                case 16:
                    $arrOrders['packing']['picklists'][$objLine->picklist_ref]['data'][] = $objLine;
                    $arrOrders['packing']['count'] ++;
                    break;
            }
        }

        return $arrOrders;
    }

    public function getPicklist($picklistRef) {
        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('Backorder');
        $items = $this->orderLineRepo->listOrderProducts()->where('picklist_ref', $picklistRef);
        $items = $items->transform(function (\App\Shop\OrderProducts\OrderProduct $order) {
                    return $order;
                })->all();
        $items = $this->orderLineRepo->paginateArrayResults($items, 10);
        $channels = $this->channelRepo->listChannels();
        return view('admin.warehouse.getPicklist', [
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
        $newStatus = $this->orderStatusRepo->findByName('Picking');

        $arrErrors = [];
        $arrSuccesses = [];

        if ($order->total_paid <= 0 || empty($order->payment)) {
            $message = 'Failed to pick order as payment information is incorrect or missing';
            $data = [
                'content' => $message,
                'user_id' => auth()->guard('admin')->user()->id
            ];
            $postRepo = new OrderCommentRepository($order);
            $postRepo->createComment($data);
            $arrErrors[$request->orderId][] = $message;
            echo json_encode(['http_code' => 400, 'FAILURES' => $arrErrors]);
            die;
        }

        try {
            $objOrderLineRepo = new OrderProductRepository($objLine);
            $objOrderLineRepo->updateOrderProduct(['status' => $newStatus->id]);
        } catch (Exception $ex) {
            $arrErrors[$request->orderId][] = $ex->getMessage();
            echo json_encode(['http_code' => 400, 'FAILURES' => $arrErrors]);
            die;
        }

        $arrSuccesses[$request->orderId][] = 'Order has been updated successfully';


        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 0) {
            $orderRepo = new OrderRepository($order);
            $orderRepo->updateOrder(['order_status_id' => $newStatus->id]);
        }

        echo json_encode(['http_code' => 200, 'FAILURES' => $arrErrors, 'SUCCESS' => $arrSuccesses]);
        die;
    }

    /**
     * 
     * @param WarehouseRequest $request
     */
    public function packOrder(WarehouseRequest $request) {
        $arrErrors = [];
        $arrSuccesses = [];

        try {
            $order = $this->orderRepo->findOrderById($request->orderId);
            $channel = $this->channelRepo->findChannelById($order->channel);
            $objLine = $this->orderLineRepo->findOrderProductById($request->lineId);
            $newStatus = $this->orderStatusRepo->findByName('Packing');
            $objOrderLineRepo = new OrderProductRepository($objLine);
            $objOrderLineRepo->updateOrderProduct(['status' => $newStatus->id]);
        } catch (Exception $ex) {
            $arrErrors[$request->orderId][] = $ex->getMessage();
            echo json_encode(['http_code' => 400, 'FAILURES' => $arrErrors]);
            die;
        }

        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 0) {
            $orderRepo = new OrderRepository($order);
            $orderRepo->updateOrder(['order_status_id' => $newStatus->id]);
        }

        $arrSuccesses[$request->orderId][] = 'Order has been updated successfully';
        echo json_encode(['http_code' => 200, 'FAILURES' => $arrErrors, 'SUCCESS' => $arrSuccesses]);
        die;
    }

    /**
     * 
     * @param WarehouseRequest $request
     */
    public function dispatchOrder(WarehouseRequest $request) {
        $arrErrors = [];
        $arrSuccesses = [];

        try {
            $order = $this->orderRepo->findOrderById($request->orderId);
            $channel = $this->channelRepo->findChannelById($order->channel);
            $objLine = $this->orderLineRepo->findOrderProductById($request->lineId);
            $newStatus = $this->orderStatusRepo->findByName('Dispatch');
            $completeStatus = $this->orderStatusRepo->findByName('Order Completed');
            $productRepo = new ProductRepository(new Product);
            $objProduct = $productRepo->findProductById($objLine->product_id);
            $quantity = $objProduct->quantity - $objLine->quantity;
            $reserved_stock = $objProduct->reserved_stock - $objLine->quantity;
            $objProductRepo = new ProductRepository($objProduct);
            $objProductRepo->updateProduct(['quantity' => $quantity, 'reserved_stock' => $reserved_stock]);
            $objOrderLineRepo = new OrderProductRepository($objLine);
            $objOrderLineRepo->updateOrderProduct(
                    ['status' => $newStatus->id,
                        'dispatch_date' => date('Y-m-d')
                    ]
            );
        } catch (Exception $ex) {
            $arrErrors[$request->orderId][] = $ex->getMessage();
            echo json_encode(['http_code' => 400, 'FAILURES' => $arrErrors]);
            die;
        }



        (new \App\RabbitMq\Worker('dispatch'))->execute($request->lineId);
        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 0) {
            $order->order_status_id = $completeStatus->id;
            $order->save();
            //complete order
        }

        $arrSuccesses[$request->orderId][] = 'Order has been updated successfully';
        echo json_encode(['http_code' => 200, 'FAILURES' => $arrErrors, 'SUCCESS' => $arrSuccesses]);
        die;
    }

    /**
     * Generate order invoice
     *
     * @param int $id
     * @return mixed
     */
    public function generateDispatchNote(int $id) {
        $order = $this->orderRepo->findOrderById($id);
        $channel = $this->channelRepo->findChannelById($order->channel);
        $data = [
            'order' => $order,
            'products' => $order->products,
            'customer' => $order->customer,
            'courier' => $order->courier,
            'address' => $this->transformAddress($order->address),
            'status' => $order->orderStatus,
            'channel' => $channel
        ];
        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadView('dispatchNote.dispatchNote', $data)->stream();
        return $pdf->stream();
    }

    /**
     * Generate order invoice
     *
     * @param int $id
     * @return mixed
     */
    public function generatePicklist(int $id) {
        $order = $this->orderRepo->findOrderById($id);
        $channel = $this->channelRepo->findChannelById($order->channel);
        $data = [
            'order' => $order,
            'products' => $order->products,
            'customer' => $order->customer,
            'courier' => $order->courier,
            'address' => $this->transformAddress($order->address),
            'status' => $order->orderStatus,
            'channel' => $channel
        ];
        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadView('pickingList.pickingList', $data)->stream();
        return $pdf->stream();
    }

}

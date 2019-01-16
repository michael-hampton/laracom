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
        //$picking = $this->orderArrayByPicklist($this->orderLineRepo->listOrderProducts()->where('status', 15));
        //$packing = $this->orderArrayByPicklist($this->orderLineRepo->listOrderProducts()->where('status', 16));
        //$dispatch = $this->orderArrayByPicklist($this->orderLineRepo->listOrderProducts()->where('status', 17));

        $channels = $this->channelRepo->listChannels();

        return view('admin.warehouse.index', [
            'arrLines' => $arrLines,
            //'picking' => $picking,
            //'packing' => $packing,
            //'dispatch' => $dispatch,
            'channels' => $channels
                ]
        );
    }

    private function orderArrayByPicklist($arrLines) {

        $arrOrders = ['pending' => [], 'picking' => [], 'packing' => [], 'dispatch' => []];

        foreach ($arrLines as $objLine) {
            
            switch($objLine->status) {
               
                case 5:
                    $arrOrders['pending'][$objLine->picklist_ref]['data'][] = $objLine;
                    $arrOrders['pending'][$objLine->picklist_ref]['count']++;
                    break;
                
                case 15:
                    $arrOrders['pending'][$objLine->picklist_ref]['data'][] = $objLine;
                    break;
                    
                case 16:
                    $arrOrders['pending'][$objLine->picklist_ref]['data'][] = $objLine;
                    break;
                    
                case 17:
                    $arrOrders['pending'][$objLine->picklist_ref]['data'][] = $objLine;
                    break;
                    
                    
            }
            $arrOrders[$objLine->picklist_ref][] = $objLine;
        }

        return $arrOrders;
    }

    public function getPicklist($status = 5) {
        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('Backorder');
        $items = $this->orderLineRepo->listOrderProducts()->where('status', $status);
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
        if ($order->total_paid <= 0 || empty($order->payment)) {
            $data = [
                'content' => 'Failed to pick order as payment information is incorrect or missing',
                'user_id' => auth()->guard('admin')->user()->id
            ];
            $postRepo = new OrderCommentRepository($order);
            $postRepo->createComment($data);
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
        $newStatus = $this->orderStatusRepo->findByName('Packing');
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

        (new \App\RabbitMq\Worker('dispatch'))->execute($request->lineId);
        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 0) {
            $order->order_status_id = $completeStatus->id;
            $order->save();
            //complete order
        }
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

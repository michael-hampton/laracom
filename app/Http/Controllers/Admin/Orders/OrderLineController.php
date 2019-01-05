<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\OrderProducts\Requests\UpdateOrderProductRequest;
use App\Shop\Comments\OrderCommentRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrderLineController extends Controller {

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
     * @var OrderStatusRepositoryInterface
     */
    private $orderStatusRepo;

    public function __construct(
    OrderRepositoryInterface $orderRepository, OrderStatusRepositoryInterface $orderStatusRepository, OrderProductRepositoryInterface $orderProductRepository, ProductRepositoryInterface $productRepository
    ) {
        $this->orderRepo = $orderRepository;
        $this->orderStatusRepo = $orderStatusRepository;
        $this->orderLineRepo = $orderProductRepository;
        $this->productRepo = $productRepository;

        //$this->middleware(['permission:update-order, guard:employee'], ['only' => ['edit', 'update']]);
    }

    /**
     * 
     * @param Request $request
     */
    public function updateLineStatus(Request $request) {

        $orderProduct = $this->orderLineRepo->findOrderProductById($request->line_id);
        $orderProductRepo = new OrderProductRepository($orderProduct);

        $orderProductRepo->updateOrderProduct(['status' => $request->status], $request->line_id);
        return redirect()->route('admin.orders.edit', $request->order_id);
    }

    /**
     * 
     * @param UpdateOrderProductRequest $request
     */
    public function update(UpdateOrderProductRequest $request) {

        $orderProduct = $this->orderLineRepo->findOrderProductById($request->lineId);

        $orderProductRepo = new OrderProductRepository($orderProduct);

        $product = $this->productRepo->findProductById($request->productId);

        $orderProductRepo->updateProduct($product, $orderProduct);

        $data = [
        'content' => $orderProduct->product_name . ' changed to ' $product->name,
        'user_id' => auth()->guard('admin')->user()->id
        ];

        $postRepo = new OrderCommentRepository($order);
        $postRepo->createComment($data);

        return redirect()->route('admin.orders.edit', $request->orderId);
    }

    public function search(Request $request) {

        $channels = $this->channelRepo->listChannels();
        $statuses = $this->orderStatusRepo->listOrderStatuses();
        $couriers = $this->courierRepo->listCouriers();
        $customers = $this->customerRepo->listCustomers();
        $list = $this->orderLineRepo->searchOrderProducts($request);
        $orders = $this->orderLineRepo->paginateArrayResults($this->transFormOrder($list), 10);

        return view('admin.orders.list', [
            'orders' => $orders,
            'channels' => $channels,
            'statuses' => $statuses,
            'couriers' => $couriers,
            'customers' => $customers
                ]
        );
    }

    public function allocateStock() {
        
        $productRepo = new ProductRepository();
        $arrDone = [];
        
        foreach($arrLines as $arrLine) {
              $arrProducts = $this->orderLineRepo->listOrderProducts('order_id', $arrLine['order_id'])->where('status', 11);
            
            $total = count($arrProducts);
            
            foreach($arrProducts as $objProductLine) {
                
                $product = $productRepo->findProductById($objProductLine->product_id);
                
                if($product->quantity > $objProductLine->quantity) {
                    
                    $total--;
                }
            }
            
            if($total > 0 && $channel->partial_shipment === 0) {
                
            } elseif($total > 0 && $channel->partial_shipment === 1) {
                $objLine2 = $this->orderLineRepo->findOrderProductById($arrLine['line_id]);
                $objLine2->status = 12;
                $objLine2->save();
            } else {
                foreach($arrProducts as $objLine2) {
                    $objLine->status = 12;
                    $objLine->save();
                }
                
                $arrDone[] = $arrLine['order_id'];
                
                return true;
            }
        }

        die('do allocation');
    }

}

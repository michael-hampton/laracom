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
            'content' => $orderProduct->product_name . ' changed to ' . $product->name,
            'user_id' => auth()->guard('admin')->user()->id
        ];

        $postRepo = new OrderCommentRepository($order);
        $postRepo->createComment($data);

        return redirect()->route('admin.orders.edit', $request->orderId);
    }

    public function search(Request $request) {

        $channels = $this->channelRepo->listChannels();
        $statuses = $this->orderStatusRepo->listOrderStatuses();
        $list = $this->orderLineRepo->searchOrderProducts($request)->transform(function (\App\Shop\OrderProducts\OrderProduct $order) {

                    return $order;
                })->all();

        $items = $this->orderLineRepo->paginateArrayResults($list, 10);

        return view('admin.orders.backorders', [
            'items' => $items,
            'channels' => $channels,
            'statuses' => $statuses
                ]
        );
    }

    /**
     * 
     * @param Request $request
     */
    public function allocateStock(Request $request) {

        $productRepo = new ProductRepository(new Product);
        $os = $this->orderStatusRepo->findByName('Backorder');
        $objNewStatus = $this->orderStatusRepo->findByName('ordered');
        $arrDone = [];

        foreach ($request->lineIds as $arrLine) {

            if (in_array($arrLine['order_id'], $arrDone)) {

                continue;
            }

            $order = $this->orderRepo->findOrderById($arrLine['order_id']);
            $channel = $this->channelRepo->findChannelById($order->channel);

            $arrProducts = $this->orderLineRepo->listOrderProducts()->where('order_id', $order->id)->where('status', $os->id);

            $total = $arrProducts->count();

            foreach ($arrProducts as $objProductLine) {

                $product = $productRepo->findProductById($objProductLine->product_id);

                if ($product->quantity > $objProductLine->quantity) {

                    $total--;
                }
            }


            if ($total > 0 && $channel->partial_shipment === 0) {

                die('reject');
            } elseif ($total > 0 && $channel->partial_shipment === 1) {

                $objLine2 = $this->orderLineRepo->findOrderProductById($arrLine['line_id']);
                $orderLineRepo = new OrderProductRepository(new OrderProduct);
                $orderLineRepo->update(['status' => $objNewStatus->id], $arrLine['line_id']);
            } else {

                foreach ($arrProducts as $objLine2) {
                    $objLine2->status = $objNewStatus->id;
                    $objLine2->save();
                }

                $order->order_status_id = $objNewStatus->id;
                $order->save();
                $arrDone[] = $arrLine['order_id'];
            }
        }
    }

}

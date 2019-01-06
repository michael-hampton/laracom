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
        $list = $this->orderLineRepo->searchOrderProducts($request)->transform(function (OrderProduct $order) {

                    return $order;
                })->all();

        $items = $this->orderLineRepo->paginateArrayResults($list, 10);


        $module = $request->module;

        return view('admin.orders.' . $module, [
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
    public function doAllocation(Request $request) {

        $productRepo = new ProductRepository(new Product);
        $os = $this->orderStatusRepo->findByName('Waiting Allocation');
        $objNewStatus = $this->orderStatusRepo->findByName('ordered');
        $arrDone = [];
        $arrFailed = [];

        foreach ($request->lineIds as $arrLine) {

            if (in_array($arrLine['order_id'], $arrDone)) {

                continue;
            }

            $order = $this->orderRepo->findOrderById($arrLine['order_id']);
            $channel = $this->channelRepo->findChannelById($order->channel);

            // get all backoredered lines for order
            $arrProducts = $this->orderLineRepo->listOrderProducts()->where('order_id', $order->id)->where('status', $os->id);

            $originalTotal = $arrProducts->count();
            $total = $arrProducts->count();
            $intCantMove = 0;

            // work out how many lines can be moved

            foreach ($arrProducts as $objProductLine) {

                $product = $productRepo->findProductById($objProductLine->product_id);


                if ($product->quantity > $objProductLine->quantity) {

                    $total--;
                } else {
                    $intCantMove++;
                }
            }

            // none can move
            if (($intCantMove === $originalTotal) || ($total > 0 && $channel->partial_shipment === 0)) {
                $arrFailed[] = $arrLine['line_id'];
            } elseif ($total > 0 && $channel->partial_shipment === 1) {
                
            } else {
                
            }
        }
    }

    /**
     * 
     * @param Request $request
     */
    public function processBackorders(Request $request) {

        $productRepo = new ProductRepository(new Product);
        $os = $this->orderStatusRepo->findByName('Backorder');
        $objNewStatus = $this->orderStatusRepo->findByName('ordered');
        $arrDone = [];
        $arrFailed = [];

        foreach ($request->lineIds as $arrLine) {

            if (in_array($arrLine['order_id'], $arrDone)) {

                continue;
            }

            $order = $this->orderRepo->findOrderById($arrLine['order_id']);
            $channel = $this->channelRepo->findChannelById($order->channel);

            // get all backoredered lines for order
            $arrProducts = $this->orderLineRepo->listOrderProducts()->where('order_id', $order->id)->where('status', $os->id);

            $total = $arrProducts->count();

            // work out how many lines can be moved

            foreach ($arrProducts as $objProductLine) {

                $product = $productRepo->findProductById($objProductLine->product_id);


                if ($product->quantity > $objProductLine->quantity) {

                    $total--;
                }
            }


            if ($total > 0 && $channel->partial_shipment === 0) {

                // cant complete because there are more than 1 line that are backordered and no partial shipping allowed
                $arrFailed[] = $arrLine['line_id'];

                // if partial shipping allowed and more than 1 line backordered then move single line
            } elseif ($total > 0 && $channel->partial_shipment === 1) {

                $objLine2 = $this->orderLineRepo->findOrderProductById($arrLine['line_id']);
                $objProduct = $productRepo->findProductById($objLine2->product_id);

                // check enough quantity to fulfil line if not reject
                if ($objProduct->quantity > $objLine2->quantity) {
                    // update stock
                    $reserved_stock = $objProduct->reserved_stock - $objLine2->quantity;
                    $quantity = $objProduct->quantity - $objLine2->quantity;
                    $objProductRepo = new ProductRepository($objProduct);
                    $objProductRepo->updateProduct(['reserved_stock' => $reserved_stock, 'quantity' => $quantity]);

                    // update line status
                    $orderLineRepo = new OrderProductRepository(new OrderProduct);
                    $orderLineRepo->update(['status' => $objNewStatus->id], $arrLine['line_id']);
                } else {
                    $arrFailed[] = $arrLine['line_id'];
                }

                // if all can be backordered move all
            } else {

                foreach ($arrProducts as $objLine2) {

                    $objProduct = $productRepo->findProductById($objLine2->product_id);

                    if ($objProduct->quantity > $objLine2->quantity) {
                        $reserved_stock = $objProduct->reserved_stock - $objLine2->quantity;
                        $quantity = $objProduct->quantity - $objLine2->quantity;

                        $objProductRepo = new ProductRepository($objProduct);
                        $objProductRepo->updateProduct(['reserved_stock' => $reserved_stock, 'quantity' => $quantity]);

                        $objLine2->status = $objNewStatus->id;
                        $objLine2->save();
                    } else {
                        $arrFailed[] = $arrLine['line_id'];
                    }
                }

                $order->order_status_id = $objNewStatus->id;
                $order->save();
                $arrDone[] = $arrLine['order_id'];
            }
        }

        if (count($arrFailed) > 0) {
            return response()->json(['error' => 'we failed to process the following line ids ' . implode(',', $arrFailed)], 404);
        }
    }

}

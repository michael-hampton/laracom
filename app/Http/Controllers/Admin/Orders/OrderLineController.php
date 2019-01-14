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
use App\Search\OrderProductSearch;
use App\Shop\Couriers\Courier;
use App\Shop\Couriers\Repositories\CourierRepository;
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


        foreach ($request->form as $arrData) {

            $lineId = $arrData['line_id'];
            unset($arrData['line_id']);

            $orderProduct = $this->orderLineRepo->findOrderProductById($lineId);

            $orderProductRepo = new OrderProductRepository($orderProduct);

            $orderProductRepo->updateOrderProduct($arrData, $lineId);
        }
        return redirect()->route('admin.orders.edit', $request->order_id);
    }

    /**
     * 
     * @param UpdateOrderProductRequest $request
     */
    public function update(Request $request) {

        $arrResponse = array(
            'http_code' => 201,
        );

        $blError = false;
        $arrErrors = [];
        $arrSuccess = [];

        foreach ($request->form as $arrLine) {

            try {
                $orderProduct = $this->orderLineRepo->findOrderProductById($arrLine['line_id']);

                $orderId = $orderProduct->order_id;

                $order = $this->orderRepo->findOrderById($orderProduct->order_id);
                $postRepo = new OrderCommentRepository($order);
                $orderProductRepo = new OrderProductRepository($orderProduct);
                $product = $this->productRepo->findProductById($arrLine['product_id']);
                $orderProductRepo->updateProduct($product, $orderProduct);
                $productRepo = new ProductRepository($product);
                $reservedStock = $product->reserved_stock + $orderProduct->quantity;

                $productRepo->update(['reserved_stock' => $reservedStock], $product->id);

                $arrSuccess[$orderId][] = "order {$orderId} line {$arrLine['line_id']} was updated successfully";
            } catch (\Exception $e) {
                $arrErrors['errors'][$orderId][] = $e->getMessage();
                $blError = true;
            }

            $data = [
                'content' => $orderProduct->product_name . ' changed to ' . $product->name,
                'user_id' => auth()->guard('admin')->user()->id
            ];

            $postRepo->createComment($data);
        }

        if ($blError === true) {
            $arrResponse['details']['FAILURES'] = $arrErrors;
        } else {
            $arrResponse['details']['SUCCESS'] = $arrSuccess;
        }

        echo json_encode($arrResponse);
        die;
    }

    public function search(Request $request) {

        $module = $request->module;
        unset($request->module);

        $channels = $this->channelRepo->listChannels();
        $statuses = $this->orderStatusRepo->listOrderStatuses();

        $courierRepo = new CourierRepository(new Courier);
        $couriers = $courierRepo->listCouriers();

        $arrProducts = $this->productRepo->listProducts();

        $list = OrderProductSearch::apply($request);

        $list = $list->transform(function (OrderProduct $order) {

                    return $order;
                })->all();

        $items = $this->orderLineRepo->paginateArrayResults($list, 10);




        return view('admin.orders.' . $module, [
            'items' => $items,
            'channels' => $channels,
            'statuses' => $statuses,
            'couriers' => $couriers,
            'products' => $arrProducts
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
        $blError = false;

        foreach ($request->lineIds as $orderId => $arrLines) {

            foreach ($arrLines as $lineId) {
                if (in_array($orderId, $arrDone)) {

                    continue;
                }

                $order = $this->orderRepo->findOrderById($orderId);
                $channel = $this->channelRepo->findChannelById($order->channel);


                $statusCount = $this->orderLineRepo->chekIfAllLineStatusesAreEqual($order, $os->id);

                $arrProducts = $this->orderLineRepo->listOrderProducts()->where('order_id', $order->id);

                if ($statusCount === 0) {

                    foreach ($arrProducts as $objLine) {

                        if ($objLine->status !== $os->id) {

                            continue;
                        }

                        $objProduct = $productRepo->findProductById($objLine->product_id);

                        if ($channel->allocate_on_order === 1 || $order->payment === 'import') {
                            // check enough quantity to fulfil line if not reject
                            // update stock
                            $reserved_stock = $objProduct->reserved_stock + $objLine->quantity;

                            //$quantity = $objProduct->quantity - $objLine2->quantity;
                            try {
                                $objProductRepo = new ProductRepository($objProduct);
                                $objProductRepo->updateProduct(['reserved_stock' => $reserved_stock]);
                            } catch (\Exception $e) {
                                $arrFailed[$lineId][] = $e->getMessage();
                                $blError = true;
                                continue;
                            }
                        }

                        try {
                            // update line status
                            $orderLineRepo = new OrderProductRepository(new OrderProduct);
                            $orderLineRepo->update(['status' => $objNewStatus->id], $lineId);

                            $order->order_status_id = $objNewStatus->id;
                            $order->save();
                        } catch (\Exception $e) {
                            $arrFailed[$lineId][] = $e->getMessage();
                            $blError = true;
                        }
                    }
                } elseif ($channel->partial_shipment === 1) {

                    $objLine = $this->orderLineRepo->findOrderProductById($lineId);
                    $objProduct = $productRepo->findProductById($objLine->product_id);

                    if ($channel->allocate_on_order === 1 || $order->payment === 'import') {
                        // update stock
                        $reserved_stock = $objProduct->reserved_stock + $objLine->quantity;
                        //$quantity = $objProduct->quantity - $objLine2->quantity;

                        try {
                            $objProductRepo = new ProductRepository($objProduct);
                            $objProductRepo->updateProduct(['reserved_stock' => $reserved_stock]);
                        } catch (\Exception $e) {
                            $arrFailed[$lineId][] = $e->getMessage();
                            $blError = true;
                        }
                    }

                    try {
                        // update line status
                        $orderLineRepo = new OrderProductRepository(new OrderProduct);
                        $orderLineRepo->update(['status' => $objNewStatus->id], $lineId);
                    } catch (\Exception $e) {
                        $arrFailed[$lineId][] = $e->getMessage();
                        $blError = true;
                    }
                }

                $arrDone[$lineId] = "Order {$orderId} Line Id {$lineId} was updated successfully";
            }
        }

        $http_code = $blError === true ? 400 : 200;
        echo json_encode(['http_code' => $http_code, 'FAILURES' => $arrFailed, 'SUCCESS' => $arrDone]);
        die;
    }

    /**
     * 
     * @param Request $request
     */
    public function processBackorders(Request $request) {

        $productRepo = new ProductRepository(new Product);
        $os = $this->orderStatusRepo->findByName('Backorder');
        $objNewStatus = $this->orderStatusRepo->findByName('Waiting Allocation');
        $arrDone = [];
        $arrFailed = [];
        $blError = false;

        foreach ($request->lineIds as $orderId => $arrLines) {

            foreach ($arrLines as $lineId) {
                if (in_array($orderId, $arrDone)) {

                    continue;
                }

                $order = $this->orderRepo->findOrderById($orderId);
                $channel = $this->channelRepo->findChannelById($order->channel);

                // get all backoredered lines for order
                $arrProducts = $this->orderLineRepo->listOrderProducts()->where('order_id', $order->id);

                $total = $arrProducts->count();
                $backorderCount = 0;
                $intCantMove = 0;

                if ($total === 0) {
                    continue;
                }

                // work out how many lines can be moved

                foreach ($arrProducts as $objProductLine) {

                    $product = $productRepo->findProductById($objProductLine->product_id);

                    $availiableQty = $product->quantity - $product->reserved_stock;

                    if ($objProductLine->status === 11 && $availiableQty <= $objProductLine->quantity) {

                        $intCantMove++;
                    }

                    if ($objProductLine->status === 11) {
                        $backorderCount++;
                    }
                }


                if ($total > $backorderCount && $channel->partial_shipment === 0) {
                    
                    // cant complete because there are more than 1 line that are backordered and no partial shipping allowed
                    $arrFailed[$lineId][] = 'Unable to move';
                    $blError = true;

                    // if partial shipping allowed and more than 1 line backordered then move single line
                } elseif ($intCantMove === 0 && $backorderCount > 1) {
                    
                    foreach ($arrProducts as $objLine2) {

                        $objProduct = $productRepo->findProductById($objLine2->product_id);

                        $availiableQty = $objProduct->quantity - $objProduct->reserved_stock;

                        if ($availiableQty > $objLine2->quantity) {

                            try {
                                $reserved_stock = $objProduct->reserved_stock - $objLine2->quantity;
                                //$quantity = $objProduct->quantity - $objLine2->quantity;

                                $objProductRepo = new ProductRepository($objProduct);
                                $objProductRepo->updateProduct(['reserved_stock' => $reserved_stock]);

                                $objLine2->status = $objNewStatus->id;
                                $objLine2->save();
                            } catch (\Exception $e) {
                                $arrFailed[$lineId][] = $e->getMessage();
                                $blError = true;
                                continue;
                            }
                        } else {
                            $arrFailed[$lineId][] = 'unable to move';
                            $blError = true;
                        }
                    }

                    try {
                        $order->order_status_id = $objNewStatus->id;
                        $order->save();
                        
                    } catch (\Exception $e) {
                        $arrFailed[$lineId][] = $e->getMessage();
                        $blError = true;
                    }
                } elseif (($backorderCount === $total && $backorderCount === 1) || $channel->partial_shipment === 1) {
                                        
                    $objLine2 = $this->orderLineRepo->findOrderProductById($lineId);
                    $objProduct = $productRepo->findProductById($objLine2->product_id);

                    $availiableQty = $objProduct->quantity - $objProduct->reserved_stock;
                    

                    // check enough quantity to fulfil line if not reject
                    if ($availiableQty > $objLine2->quantity) {
                        
                        try {
                            // update stock
                            $reserved_stock = $objProduct->reserved_stock + $objLine2->quantity;
                            //$quantity = $objProduct->quantity - $objLine2->quantity;
                            $objProductRepo = new ProductRepository($objProduct);
                            $objProductRepo->updateProduct(['reserved_stock' => $reserved_stock]);

                            // update line status
                            $orderLineRepo = new OrderProductRepository(new OrderProduct);
                            $orderLineRepo->update(['status' => $objNewStatus->id], $lineId);


                            if ($total === 1 && $backorderCount === 1) {
                                $order->order_status_id = $objNewStatus->id;
                                $order->save();
                            }
                        } catch (\Exception $e) {
                                                                                    
                            $arrFailed[$lineId][] = $e->getMessage();
                            $blError = true;
                            continue;
                        }
                    } else {

                        $arrFailed[$lineId] = 'No quantity availiable for products';
                        $blError = true;
                    }
                }
                
                $arrDone[$lineId] = "Order {$orderId} Line {$lineId} was updated successfully";
            }
        }

        $http_code = $blError === true ? 400 : 200;
        echo json_encode(['http_code' => $http_code, 'FAILURES' => $arrFailed, 'SUCCESS' => $arrDone]);
        die;
    }

}

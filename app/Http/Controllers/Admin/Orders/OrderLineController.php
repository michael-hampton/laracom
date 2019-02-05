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
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use App\Shop\Orders\Order;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\Couriers\Courier;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Shop\Orders\Requests\UpdateLineRequest;
use Illuminate\Support\Facades\Validator;

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

        $arrErrors = [];

        foreach ($request->form as $arrData) {

            $validator = Validator::make($arrData, (new UpdateLineRequest())->rules());

            // Validate the input and return correct response
            if ($validator->fails()) {
                return response()->json(['http_code' => 400, 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $lineId = $arrData['line_id'];
            unset($arrData['line_id']);

            try {

                $orderProduct = $this->orderLineRepo->findOrderProductById($lineId);

                $orderProductRepo = new OrderProductRepository($orderProduct);

                $orderProductRepo->updateOrderProduct($arrData, $lineId);
            } catch (\Exception $e) {
                $arrErrors[$lineId][] = $e->getMessage();
            }
        }

        if (!empty($arrErrors)) {
            return response()->json(['http_code' => 400, 'errors' => $arrErrors]);
        }

        return response()->json(['http_code' => 200]);
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

        return response()->json($arrResponse);
    }

    public function search(Request $request) {

        $orders = $this->orderRepo->listOrders('is_priority', 'desc')->keyBy('id');
        $orders = $this->transFormOrder($orders);

        $arrProducts = $this->productRepo->listProducts()->keyBy('id');

        $list = OrderProductSearch::apply($request);

        $list = $list->transform(function (OrderProduct $order) {

                    return $order;
                })->all();

        $items = $this->orderLineRepo->paginateArrayResults($list, 10);

        return view('admin.order-lines.search', [
            'items' => $items,
            'orders' => $orders,
            'products' => $arrProducts
                ]
        );
    }

    /**
     * @param Collection $list
     * @return array
     */
    private function transFormOrder(Collection $list) {
        $courierRepo = new CourierRepository(new Courier());
        $customerRepo = new CustomerRepository(new Customer());
        $orderStatusRepo = new OrderStatusRepository(new OrderStatus());
        return $list->transform(function (Order $order) use ($courierRepo, $customerRepo, $orderStatusRepo) {
                    $order->courier = $courierRepo->findCourierById($order->courier_id);
                    $order->customer = $customerRepo->findCustomerById($order->customer_id);
                    $order->status = $orderStatusRepo->findOrderStatusById($order->order_status_id);
                    $order->channel = $this->channelRepo->findChannelById($order->channel);
                    return $order;
                })->all();
    }

    private function generatePIN(int $digits = 4) {
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while ($i < $digits) {
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }
        return $pin;
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

        $picklistRef = $this->generatePIN();

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

                        if ($channel->allocate_on_order === 0 || $order->payment === 'import') {
                            // check enough quantity to fulfil line if not reject
                            // update stock

                            $quantity = $objProduct->quantity - $objProduct->reserved_stock;
                            
                            if($objLine->quantity < $quantity) {
                                $arrFailed[$lineId][] = $e->getMessage();
                                $blError = true;
                                continue;
                            }
                            
                            $reserved_stock = $objProduct->reserved_stock + $objLine->quantity;
                            if(!$this->updateReservedStock($reserved_stock, $objProduct)) {
                                $arrFailed[$lineId][] = 'failed to update stock';
                            }
                            
                        }

                        try {
                            // update line status
                            $orderLineRepo = new OrderProductRepository(new OrderProduct);
                            $orderLineRepo->update(['status' => $objNewStatus->id, 'picklist_ref' => $picklistRef], $lineId);

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

                         if(!$this->updateReservedStock($reserved_stock, $objProduct)) {
                                $arrFailed[$lineId][] = 'failed to update stock';
                            }
                    }

                    if(!$this->addToPicklist()) {
                        
                    }
                }

                $arrDone[$lineId] = "Order {$orderId} Line Id {$lineId} was updated successfully";
            }
        }

        $http_code = $blError === true ? 400 : 200;
        return response()->json(['http_code' => $http_code, 'FAILURES' => $arrFailed, 'SUCCESS' => $arrDone]);
    }
    
    private function addToPicklist() {
        try {
                        // update line status
                        $orderLineRepo = new OrderProductRepository(new OrderProduct);
                        $orderLineRepo->update(['status' => $objNewStatus->id, 'picklist_ref' => $picklistRef], $lineId);
                    } catch (\Exception $e) {
                        $arrFailed[$lineId][] = $e->getMessage();
                        $blError = true;
                    }
    }
    
    private function updateReservedStock($reserved_stock, Product $objProduct) {
        try {
                                $objProductRepo = new ProductRepository($objProduct);
                                $objProductRepo->updateProduct(['reserved_stock' => $reserved_stock]);
                            } catch (\Exception $e) {
                                return false;
                                
                            }
        
        return true;
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

                    if ($objProductLine->status === $os->id && $availiableQty <= $objProductLine->quantity) {

                        $intCantMove++;
                    }

                    if ($objProductLine->status === $os->id) {
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

                    if ($availiableQty < $objLine2->quantity) {
                        $arrFailed[$lineId] = 'No quantity availiable for products';
                        $blError = true;
                        continue;
                        //return response()->json(['http_code' => 400, 'FAILURES' => $arrFailed, 'SUCCESS' => $arrDone]);
                    }

                    // check enough quantity to fulfil line if not reject
        
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
                }

                $arrDone[$lineId] = "Order {$orderId} Line {$lineId} was updated successfully";
            }
        }

        $http_code = $blError === true ? 400 : 200;
        return response()->json(['http_code' => $http_code, 'FAILURES' => $arrFailed, 'SUCCESS' => $arrDone]);
    }

}

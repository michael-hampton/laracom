<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Channels\Channel;
use App\Shop\Channels\PaymentProvider;
use App\Shop\Channels\Repositories\PaymentProviderRepository;
use App\Shop\Channels\Repositories\ChannelPaymentDetailsRepository;
use App\Shop\Channels\ChannelPaymentDetails;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Product;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use App\Shop\Channels\Repositories\ChannelTemplateRepository;
use App\Shop\Channels\ChannelTemplate;
use App\Shop\Orders\Order;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\Comments\OrderCommentRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Shop\Orders\Requests\WarehouseRequest;
use App\Shop\Addresses\Transformations\AddressTransformable;
use App\Traits\OrderCommentTrait;

class WarehouseController extends Controller {

    use AddressTransformable,
        OrderCommentTrait;

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
                'count'     => 0
            ],
            'picking' => [
                'picklists' => [],
                'count'     => 0
            ],
            'packing' => [
                'picklists' => [],
                'count'     => 0
            ]
        ];

        foreach ($arrLines as $objLine)
        {

            switch ($objLine->status)
            {

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

    /**
     * 
     * @param string $picklistRef
     * @param int $status
     * @return type
     */
    public function getPicklist(string $picklistRef, int $status) {
        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('Backorder');
        $items = $this->orderLineRepo->listOrderProducts()->where('picklist_ref', $picklistRef);

        $items = $items->transform(function (\App\Shop\OrderProducts\OrderProduct $order) {
                    return $order;
                })->all();

        $items = $this->orderLineRepo->paginateArrayResults($items, 10);
        $channels = $this->channelRepo->listChannels();

        return view('admin.warehouse.getPicklist', [
            'items'        => $items,
            'status'       => $status,
            'channels'     => $channels,
            'picklist_ref' => $picklistRef
                ]
        );
    }

    /**
     * 
     * @param WarehouseRequest $request
     * @return type
     */
    public function removeOrderFromPicklist(WarehouseRequest $request) {

        try {
            $objLine = $this->orderLineRepo->findOrderProductById($request->lineId);
            $newStatus = $this->orderStatusRepo->findByName('Waiting Allocation');
            $objOrderLineRepo = new OrderProductRepository($objLine);
            $objOrderLineRepo->updateOrderProduct(['picklist_ref' => null, 'status' => $newStatus->id]);
        } catch (Exception $ex) {
            $arrErrors[$request->orderId][] = $ex->getMessage();
            return response()->json(['http_code' => 400, 'FAILURES' => $arrErrors]);
        }

        $arrSuccesses[$request->orderId][] = 'Order has been updated successfully';
        return response()->json(['http_code' => 200, 'SUCCESS' => $arrSuccesses]);
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

        if ($this->orderLineRepo->chekIfAllLineStatusesAreEqual($order, 16) > 1 && $channel->partial_shipment === 0)
        {
            $arrErrors[$request->orderId][] = 'order lines are at different statuses';
            return response()->json(['http_code' => 400, 'FAILURES' => $arrErrors]);
        }

        if ($order->total_paid <= 0 || empty($order->payment))
        {
            $message = 'Failed to pick order as payment information is incorrect or missing';
            $this->saveNewComment($order, $message);
            $arrErrors[$request->orderId][] = $message;
            return response()->json(['http_code' => 400, 'FAILURES' => $arrErrors]);
        }

        $blFailAllLines = false;
        $arrData['status'] = $newStatus->id;

        if ($request->picked_quantity != $objLine->quantity)
        {
            $intNewQuantity = (int) $objLine->quantity - (int) $request->picked_quantity;

            switch ($channel->partial_shipment)
            {
                case 1:
                    $objLine->quantity = $intNewQuantity;
                    $this->orderLineRepo->doClone($objLine, $order);
                    $arrData['quantity'] = $request->picked_quantity;
                    break;

                case 0:
                    $blFailAllLines = true;
                    break;
            }

            if ($blFailAllLines === true)
            {
                $arrLines = $this->orderLineRepo->listOrderProducts()->where('order_id', $order->id);

                foreach ($arrLines as $arrLine)
                {
                    // set to pick failed
                    $objOrderLineRepo = new OrderProductRepository($arrLine);
                    $objOrderLineRepo->updateOrderProduct(['status' => 21]);
                }

                $statusCount = $this->orderLineRepo->chekIfAllLineStatusesAreEqual($order, 21);

                if ($statusCount === 0)
                {
                    (new OrderRepository($order))->updateOrder(['order_status_id' => 21]);
                }

                $comment = 'order line updated to picklist failed';
                $this->saveNewComment($order, $comment);
                $arrErrors[$request->orderId][] = 'updated line to picklist failed';
                return response()->json(['http_code' => 400, 'FAILURES' => $arrErrors]);
            }
        }

        try {
            $objOrderLineRepo = new OrderProductRepository($objLine);
            $objOrderLineRepo->updateOrderProduct($arrData);
        } catch (Exception $ex) {
            $arrErrors[$request->orderId][] = $ex->getMessage();
            return response()->json(['http_code' => 400, 'FAILURES' => $arrErrors]);
        }

        $arrSuccesses[$request->orderId][] = 'Order has been updated successfully';

        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 0)
        {
            $orderRepo = new OrderRepository($order);
            $orderRepo->updateOrder(['order_status_id' => $newStatus->id]);
        }

        return response()->json(['http_code' => 200, 'FAILURES' => $arrErrors, 'SUCCESS' => $arrSuccesses]);
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

            if ($this->orderLineRepo->chekIfAllLineStatusesAreEqual($order, 16) > 1 && $channel->partial_shipment === 0)
            {
                $arrErrors[$request->orderId][] = 'order lines are at different statuses';
                return response()->json(['http_code' => 400, 'FAILURES' => $arrErrors]);
            }

            $objOrderLineRepo = new OrderProductRepository($objLine);
            $objOrderLineRepo->updateOrderProduct(['status' => $newStatus->id]);
        } catch (Exception $ex) {
            $arrErrors[$request->orderId][] = $ex->getMessage();
            return response()->json(['http_code' => 400, 'FAILURES' => $arrErrors]);
        }

        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 0)
        {
            $orderRepo = new OrderRepository($order);
            $orderRepo->updateOrder(['order_status_id' => $newStatus->id]);
        }

        $arrSuccesses[$request->orderId][] = 'Order has been updated successfully';
        return response()->json(['http_code' => 200, 'FAILURES' => $arrErrors, 'SUCCESS' => $arrSuccesses]);
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

            if ($this->orderLineRepo->chekIfAllLineStatusesAreEqual($order, 16) > 1 && $channel->partial_shipment === 0)
            {
                $arrErrors[$request->orderId][] = 'order lines are at different statuses';
                return response()->json(['http_code' => 400, 'FAILURES' => $arrErrors]);
            }

            $completeStatus = $this->orderStatusRepo->findByName('Order Completed');
            $productRepo = new ProductRepository(new Product);
            $objProduct = $productRepo->findProductById($objLine->product_id);
            $quantity = $objProduct->quantity - $objLine->quantity;
            $reserved_stock = $objProduct->reserved_stock - $objLine->quantity;
            $objProductRepo = new ProductRepository($objProduct);
            $objProductRepo->updateProduct(['quantity' => $quantity, 'reserved_stock' => $reserved_stock]);
            $objOrderLineRepo = new OrderProductRepository($objLine);
            $objOrderLineRepo->updateOrderProduct(
                    ['status'        => $newStatus->id,
                        'dispatch_date' => date('Y-m-d')
                    ]
            );
        } catch (Exception $ex) {
            $arrErrors[$request->orderId][] = $ex->getMessage();
            return response()->json(['http_code' => 400, 'FAILURES' => $arrErrors]);
        }

        (new \App\RabbitMq\Worker('dispatch'))->execute($request->lineId);
        if ($objOrderLineRepo->chekIfAllLineStatusesAreEqual($order, $newStatus->id) === 0)
        {
            $this->capturePayment($order, $channel);
            $order->order_status_id = $completeStatus->id;
            $order->save();
            //complete order
        }

        $arrSuccesses[$request->orderId][] = 'Order has been updated successfully';
        return response()->json(['http_code' => 200, 'FAILURES' => $arrErrors, 'SUCCESS' => $arrSuccesses]);
    }

    /**
     * 
     * @param Order $order
     * @param Channel $channel
     * @return boolean
     */
    private function capturePayment(Order $order, Channel $channel) {

        switch ($order->payment)
        {
            case 'paypal':

                $paymentProvider = (new PaymentProviderRepository(new PaymentProvider))->findByName('paypal');
                $objChannelPaymentDetails = (new ChannelPaymentDetailsRepository(new ChannelPaymentDetails))->getPaymentDetailsForChannel($channel, $paymentProvider);

                if (!(new \App\Shop\PaymentMethods\Paypal\Repositories\PayPalExpressCheckoutRepository($objChannelPaymentDetails))->capturePayment($order))
                {

                    return response()->json(['error' => 'failed to authorize'], 404); // Status code here
                }
                break;

            case 'stripe':

                $paymentProvider = (new PaymentProviderRepository(new PaymentProvider))->findByName('stripe');
                $objChannelPaymentDetails = (new ChannelPaymentDetailsRepository(new ChannelPaymentDetails))->getPaymentDetailsForChannel($channel, $paymentProvider);
                $customer = (new CustomerRepository(new Customer))->findCustomerById($order->customer->id);

                if (!(new \App\Shop\PaymentMethods\Stripe\StripeRepository($customer, $objChannelPaymentDetails))->capturePayment($order))
                {
                    return false;
                }
                break;
        }


        return true;
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
        $newStatus = $this->orderStatusRepo->findByName('Dispatch');
        $items = $this->orderLineRepo->listOrderProducts()->where('order_id', $id);
        $terms = (new ChannelTemplateRepository(new ChannelTemplate))->getTemplateForChannelBySection($channel, 2);

        $data = [
            'order'          => $order,
            'items'          => $items,
            'allowed_status' => $newStatus->id,
            'products'       => $order->products,
            'customer'       => $order->customer,
            'courier'        => $order->courier,
            'address'        => $this->transformAddress($order->address),
            'status'         => $order->orderStatus,
            'channel'        => $channel,
            'terms'          => $terms
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
    public function generatePicklist(int $picklistRef) {
        $items = $this->orderLineRepo->listOrderProducts()->where('picklist_ref', $picklistRef);
        //$channel = $this->channelRepo->findChannelById($order->channel);
        $data = [
            'items' => $items,
        ];

        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadView('pickingList.pickingList', $data)->stream();

        return $pdf->stream();
    }

}

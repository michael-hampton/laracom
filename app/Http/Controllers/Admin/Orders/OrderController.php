<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Addresses\Transformations\AddressTransformable;
use App\Shop\Orders\Requests\CreateOrderRequest;
use App\Shop\Couriers\Courier;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\VoucherCodes\VoucherCode;
use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Customers\Customer;
use App\Shop\Orders\Requests\ImportRequest;
use App\Shop\Comments\Comment;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Addresses\Repositories\AddressRepository;
use App\Shop\Addresses\Address;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Vouchers\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Orders\Order;
use App\Shop\Orders\OrderImport;
use App\Shop\CourierRates\Repositories\CourierRateRepository;
use App\Shop\CourierRates\CourierRate;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Comments\Transformers\CommentTransformer;
use App\Shop\Orders\Transformers\OrderCsvTransformable;
use App\Shop\Refunds\Repositories\Interfaces\RefundRepositoryInterface;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Shop\Comments\OrderCommentRepository;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Psr\Log\NullLogger;
use Illuminate\Events\Dispatcher;
use App\Search\OrderSearch;
use Validator;

class OrderController extends Controller {

    use AddressTransformable,
        CommentTransformer,
        OrderCsvTransformable;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepo;

    /**
     * @var CategoryRepositoryInterface
     */
    private $refundRepo;

    /**
     * @var VoucherRepositoryInterface
     */
    private $voucherRepo;

    /**
     * @var VoucherCodeRepositoryInterface
     */
    private $voucherCodeRepo;

    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepo;

    /**
     * @var CourierRepositoryInterface
     */
    private $courierRepo;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepo;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @var OrderProductRepositoryInterface
     */
    private $orderProductRepo;

    /**
     * @var OrderStatusRepositoryInterface
     */
    private $orderStatusRepo;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * 
     * @param OrderRepositoryInterface $orderRepository
     * @param CourierRepositoryInterface $courierRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderStatusRepositoryInterface $orderStatusRepository
     * @param RefundRepositoryInterface $refundRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param OrderProductRepositoryInterface $orderProductRepository
     * @param ProductRepositoryInterface $productRepository
     * @param VoucherRepositoryInterface $voucherRepository
     * @param VoucherCodeRepositoryInterface $voucherCodeRepository
     */
    public function __construct(
    OrderRepositoryInterface $orderRepository, CourierRepositoryInterface $courierRepository, AddressRepositoryInterface $addressRepository, CustomerRepositoryInterface $customerRepository, OrderStatusRepositoryInterface $orderStatusRepository, RefundRepositoryInterface $refundRepository, ChannelRepositoryInterface $channelRepository, OrderProductRepositoryInterface $orderProductRepository, ProductRepositoryInterface $productRepository, VoucherRepositoryInterface $voucherRepository, VoucherCodeRepositoryInterface $voucherCodeRepository
    ) {
        $this->orderRepo = $orderRepository;
        $this->courierRepo = $courierRepository;
        $this->addressRepo = $addressRepository;
        $this->customerRepo = $customerRepository;
        $this->orderStatusRepo = $orderStatusRepository;
        $this->refundRepo = $refundRepository;
        $this->channelRepo = $channelRepository;
        $this->orderProductRepo = $orderProductRepository;
        $this->productRepo = $productRepository;
        $this->voucherRepo = $voucherRepository;
        $this->voucherCodeRepo = $voucherCodeRepository;

        $this->middleware(['permission:update-order, guard:admin'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:create-order, guard:admin'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:import-order, guard:admin'], ['only' => ['importCsv', 'saveImport']]);
        $this->middleware(['permission:allocations-order, guard:admin'], ['only' => ['allocations']]);
        $this->middleware(['permission:backorders-order, guard:admin'], ['only' => ['backorders']]);
        $this->middleware(['permission:clone-order, guard:admin'], ['only' => ['cloneOrder']]);
        $this->middleware(['permission:view-order, guard:admin'], ['only' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $channels = $this->channelRepo->listChannels();
        $statuses = $this->orderStatusRepo->listOrderStatuses();
        $couriers = $this->courierRepo->listCouriers();
        $customers = $this->customerRepo->listCustomers();

        return view('admin.orders.list', [
            'channels'  => $channels,
            'statuses'  => $statuses,
            'couriers'  => $couriers,
            'customers' => $customers
                ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int $orderId
     * @return \Illuminate\Http\Response
     */
    public function show($orderId) {

        $order = $this->orderRepo->findOrderById($orderId);
        $order->courier = $this->courierRepo->findCourierById($order->courier_id);
        $order->address = $this->addressRepo->findAddressById($order->address_id);
        $orderRepo = new OrderRepository($order);

        $channel = $this->channelRepo->findChannelById($order->channel);
        $items = $orderRepo->listOrderedProducts();
        $arrAudits = $order->audits;

        return view('admin.orders.show', [
            'order'         => $order,
            'items'         => $items,
            'customer'      => $this->customerRepo->findCustomerById($order->customer_id),
            'currentStatus' => $this->orderStatusRepo->findOrderStatusById($order->order_status_id),
            'payment'       => $order->payment,
            'user'          => auth()->guard('admin')->user(),
            'channel'       => $channel,
            'audits'        => $arrAudits
        ]);
    }

    /**
     * @param $orderId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($orderId) {
        $order = $this->orderRepo->findOrderById($orderId);

        $order->courier = $this->courierRepo->findCourierById($order->courier_id);
        $order->address = $this->addressRepo->findAddressById($order->address_id);
        $order->channel = $this->channelRepo->findChannelById($order->channel);
        //$couriers = $this->courierRepo->listCouriers()->where('channel', $order->channel->id);
        $couriers = $this->courierRepo->listCouriers();

        $items = $this->orderProductRepo->listOrderProducts()->where('order_id', $orderId);

        $voucher = null;

        if (!empty($order->voucher_code))
        {


            $voucher = $this->voucherCodeRepo->findVoucherCodeById($order->voucher_code);
        }

        $arrProducts = $this->productRepo->listProducts()->keyBy('id');

        $arrAudits = $order->audits;

        $list = (new OrderCommentRepository($order))->listComments();

        $comments = $list->map(function (Comment $item) {
                    return $this->transformComment($item);
                })->all();

        $arrRefunds = $this->refundRepo->getRefundsForOrderByLineId($order);

        $arrStatusMapping = $this->orderStatusRepo->buildStatusMapping();

        return view('admin.orders.edit', [
            'statuses'       => $this->orderStatusRepo->listOrderStatuses(),
            'status_mapping' => $arrStatusMapping,
            'products'       => $arrProducts,
            'order'          => $order,
            'couriers'       => $couriers,
            'items'          => $items,
            'refunds'        => $arrRefunds,
            'customer'       => $this->customerRepo->findCustomerById($order->customer_id),
            'currentStatus'  => $this->orderStatusRepo->findOrderStatusById($order->order_status_id),
            'payment'        => $order->payment,
            'user'           => auth()->guard('admin')->user(),
            'audits'         => $arrAudits,
            'voucher'        => $voucher,
            'comments'       => $comments
        ]);
    }

    /**
     * @param Request $request
     * @param $orderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $orderId) {

        $order = $this->orderRepo->findOrderById($orderId);
        $orderRepo = new OrderRepository($order);
        if ($request->has('total_paid') && $request->input('total_paid') != null)
        {
            $orderData = $request->except('_method', '_token');
        }
        else
        {
            $orderData = $request->except('_method', '_token', 'total_paid');
        }
        $orderRepo->updateOrder($orderData);
        return redirect()->route('admin.orders.edit', $orderId);
    }

    public function search(Request $request) {

        $list = OrderSearch::apply($request);

        $orders = $this->orderRepo->paginateArrayResults($this->transFormOrder($list), 10);

        return view('admin.orders.search', [
            'orders' => $orders
                ]
        );
    }

    /**
     * Show the form for creating a new resource.
     * @param type $channel
     * @return type
     */
    public function create($channel = null) {
        if (!is_null($channel))
        {
            $channels = null;
            $channel = $this->channelRepo->listChannels()->where('name', $channel)->first();
            $repo = new ChannelRepository($channel);
            $products = $repo->findProducts()->where('status', 1)->all();
        }
        else
        {
            $channels = $this->channelRepo->listChannels();
            $products = $this->productRepo->listProducts()->where('status', 1);
        }

        $customers = $this->customerRepo->listCustomers();
        $couriers = $this->courierRepo->listCouriers();

        return view('admin.orders.create', [
            'selectedChannel' => isset($channel) ? $channel->id : null,
            'channels'        => $channels,
            'products'        => $products,
            'customers'       => $customers,
            'couriers'        => $couriers,
                ]
        );
    }

    /**
     *  Store a newly created resource in storage.
     * 
     * @param CreateOrderRequest $request
     * @return type
     */
    public function store(CreateOrderRequest $request) {

        $customer = $this->customerRepo->findCustomerById($request->customer);

        $customerRepo = new CustomerRepository($customer);
        $deliveryAddress = $customerRepo->findAddresses()->first();

        $channel = $this->channelRepo->findChannelById($request->channel);

        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('Waiting Allocation');

        $orderTotal = $request->total;

        $objCourierRate = new CourierRateRepository(new CourierRate);
        $courier = $this->courierRepo->findCourierById($request->courier);

        $country_id = $deliveryAddress->country_id;

        $shipping = $objCourierRate->findShippingMethod($request->total, $courier, $channel, $country_id);

        $shippingCost = 0;

        if (!empty($shipping))
        {

            $shippingCost = $shipping->cost;
        }

        $orderTotal += $shippingCost;

        $voucherAmount = 0;

        if (!empty($request->voucher_code))
        {
            $voucherCode = $this->voucherCodeRepo->getByVoucherCode($request->voucher_code);

            $voucher_id = $voucherCode->voucher_id;
            $objVoucher = $this->voucherRepo->findVoucherById($voucher_id);

            $voucherAmount = $objVoucher->amount;
        }

        $orderTotal -= $voucherAmount;

        $arrData = [
            'reference'       => md5(uniqid(mt_rand(), true) . microtime(true)),
            'courier_id'      => $request->courier,
            'customer_id'     => $customer->id,
            'voucher_id'      => !empty($request->voucher_code) ? $request->voucher_code : null,
            'voucher_code'    => isset($voucherCode) ? $voucherCode->id : null,
            'address_id'      => $deliveryAddress->id,
            'order_status_id' => $os->id,
            'delivery_method' => $shipping,
            'payment'         => 'import',
            'discounts'       => $voucherAmount,
            'total_shipping'  => $shippingCost,
            'total_products'  => count($request->products),
            'total'           => $orderTotal,
            'total_paid'      => 0,
            'channel'         => $channel,
            'tax'             => 0,
            'products'        => $request->products
        ];


        (new \App\RabbitMq\Worker('order_import'))->execute(json_encode($arrData));
        //(new \App\RabbitMq\Receiver('order_import', 'importOrder'))->listen();

        $request->session()->flash('message', 'Creation successful');
        return redirect()->route('admin.orders.index');
    }

    /**
     * Generate order invoice
     *
     * @param int $id
     * @return mixed
     */
    public function generateInvoice(int $id) {

        $order = $this->orderRepo->findOrderById($id);
        $channel = $this->channelRepo->findChannelById($order->channel);

        $data = [
            'order'    => $order,
            'products' => $order->products,
            'customer' => $order->customer,
            'courier'  => $order->courier,
            'address'  => $this->transformAddress($order->address),
            'status'   => $order->orderStatus,
            'channel'  => $channel
        ];

        $pdf = app()->make('dompdf.wrapper');
        $pdf->loadView('invoices.orders', $data)->stream();
        return $pdf->stream();
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

    /**
     * 
     * @param Request $request
     */
    public function saveComment(Request $request) {

        $order = $this->orderRepo->findOrderById($request->order_id);

        $data = [
            'content' => $request->comment,
            'user_id' => auth()->guard('admin')->user()->id
        ];

        $postRepo = new OrderCommentRepository($order);
        $postRepo->createComment($data);

        return redirect()->route('admin.orders.edit', $request->order_id);
    }

    /**
     * 
     * @param type $orderId
     */
    public function cloneOrder(Request $request) {

        $lineId = $request->line_id;


        $blError = false;

        try {
            $orderedProduct = $this->orderProductRepo->findOrderProductById($lineId);
            $channel = $this->channelRepo->findChannelById($request->channelCode);
            $order = $this->orderRepo->findOrderById($request->dbID);

            $newOrder = $this->orderRepo->cloneOrder(
                    $order, $channel, new VoucherCodeRepository(new VoucherCode), new CourierRepository(new Courier), new CustomerRepository(new Customer), new AddressRepository(new Address)
            );
        } catch (Exception $e) {
            $arrErrors['errors'][$request->dbID][] = $e->getMessage();
            $blError = true;
        }

        if (!$newOrder)
        {
            $strMessage = 'failed to create rma order';
            $arrErrors['errors'][$request->dbID][] = $strMessage;

            $data = [
                'content' => $strMessage,
                'user_id' => auth()->guard('admin')->user()->id
            ];

            $postRepo = new OrderCommentRepository($order);
            $postRepo->createComment($data);
        }

        $orderId = $newOrder->id;
        $strMessage = $orderId . 'was created as RMA';

        $productId = null;

        foreach ($request->order as $arrOrder)
        {

            if ($arrOrder['name'] == 'kondor_product_code[' . $lineId . ']')
            {
                $productId = $arrOrder['value'];
            }
        }

        if ($productId === null)
        {
            $arrErrors['errors'][$request->dbID][] = 'unable to find product';
        }

        $arrProducts[0] = [
            'id'       => $productId,
            'quantity' => $orderedProduct->quantity
        ];

        try {
            $newOrderRepo = new OrderRepository($newOrder);

            if (!$newOrderRepo->buildOrderLinesForManualOrder($arrProducts))
            {
                $strMessage .= 'failed to clone order lines';
                $arrErrors['errors'][$request->dbID][] = $strMessage;
                $blError = true;
            }
            else
            {
                $order->update(['customer_ref' => 'RMA_' . md5(uniqid(mt_rand(), true) . microtime(true))]);
            }
        } catch (Exception $e) {
            $arrErrors['errors'][$request->dbID][] = $e->getMessage();
            $blError = true;
        }

        $data = [
            'content' => $strMessage,
            'user_id' => auth()->guard('admin')->user()->id
        ];

        $postRepo = new OrderCommentRepository($order);
        $postRepo->createComment($data);

        $arrBody = array(0 => ['test']);

        $arrResponse = array(
            'body' => $arrBody
        );

        if ($blError === true)
        {
            $arrResponse['data']['details']['FAILURES'] = $arrErrors;
            return response()->json($arrResponse);
        }
        
        $arrResponse['data']['details']['SUCCESS'][$orderId] = ['order updated successfully'];

        return response()->json($arrResponse);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {

        $this->orderRepo->delete($id);

        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.channels.index');
    }

    public function backorders() {

        $channels = $this->channelRepo->listChannels();
        $couriers = $this->courierRepo->listCouriers();

        return view('admin.orders.backorders', [
            'channels' => $channels,
            'couriers' => $couriers
                ]
        );
    }

    public function allocations() {

        $channels = $this->channelRepo->listChannels();
        $couriers = $this->courierRepo->listCouriers();

        return view('admin.orders.allocations', [
            'channels' => $channels,
            'couriers' => $couriers,
                ]
        );
    }

    /**
     * 
     * @param Request $request
     */
    public function saveImport(Request $request) {

        $file_path = $request->csv_file->path();

        $objOrderImport = new OrderImport(
                $this->courierRepo, $this->orderStatusRepo, $this->channelRepo, $this->productRepo, $this->customerRepo, $this->voucherCodeRepo, new CourierRateRepository(new CourierRate), $this->voucherRepo, new \App\RabbitMq\Worker('bulk_import')
        );

        if (!$objOrderImport->isValid($file_path))
        {

            $arrErrors = $objOrderImport->getErrors();
            return response()->json(['http_code' => '400', 'arrErrors' => $arrErrors]);
        }

        return response()->json(['http_code' => '200']);
    }

    public function importCsv() {

        return view('admin.orders.importCsv');
    }

    /**
     * 
     * @param Request $request
     */
    public function export(Request $request) {

        $list = OrderSearch::apply($request);

        $arrOrders = $list->map(function (Order $item) {
                    return $this->transformOrderForCsv($item);
                })->all();

        return response()->json($arrOrders);
    }

}

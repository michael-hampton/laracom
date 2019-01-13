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
use App\Shop\CourierRates\Repositories\CourierRateRepository;
use App\Shop\CourierRates\CourierRate;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Comments\Transformers\CommentTransformer;
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

    use AddressTransformable;
    use CommentTransformer;

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

        //$this->middleware(['permission:update-order, guard:employee'], ['only' => ['edit', 'update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $list = $this->orderRepo->listOrders('is_priority', 'desc');
        $channels = $this->channelRepo->listChannels();
        $statuses = $this->orderStatusRepo->listOrderStatuses();
        $couriers = $this->courierRepo->listCouriers();
        $customers = $this->customerRepo->listCustomers();

        if (request()->has('q')) {
            $list = $this->orderRepo->searchOrder(request()->input('q') ?? '');
        }

        $orders = $this->orderRepo->paginateArrayResults($this->transFormOrder($list), 10);

        return view('admin.orders.list', [
            'orders' => $orders,
            'channels' => $channels,
            'statuses' => $statuses,
            'couriers' => $couriers,
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
            'order' => $order,
            'items' => $items,
            'customer' => $this->customerRepo->findCustomerById($order->customer_id),
            'currentStatus' => $this->orderStatusRepo->findOrderStatusById($order->order_status_id),
            'payment' => $order->payment,
            'user' => auth()->guard('admin')->user(),
            'channel' => $channel,
            'audits' => $arrAudits
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
        $couriers = $this->courierRepo->listCouriers();

        $items = $this->orderProductRepo->listOrderProducts()->where('order_id', $orderId);

        $voucher = null;

        if (!empty($order->voucher_code)) {


            $voucher = $this->voucherCodeRepo->findVoucherCodeById($order->voucher_code);
        }

        $arrProducts = $this->productRepo->listProducts();

        $arrAudits = $order->audits;

        $list = (new OrderCommentRepository($order))->listComments();

        $comments = $list->map(function (Comment $item) {
                    return $this->transformComment($item);
                })->all();

        $arrRefunds = $this->refundRepo->getRefundsForOrderByLineId($order);

        $arrStatusMapping = $this->orderStatusRepo->buildStatusMapping();

        return view('admin.orders.edit', [
            'statuses' => $this->orderStatusRepo->listOrderStatuses(),
            'status_mapping' => $arrStatusMapping,
            'products' => $arrProducts,
            'order' => $order,
            'couriers' => $couriers,
            'items' => $items,
            'refunds' => $arrRefunds,
            'customer' => $this->customerRepo->findCustomerById($order->customer_id),
            'currentStatus' => $this->orderStatusRepo->findOrderStatusById($order->order_status_id),
            'payment' => $order->payment,
            'user' => auth()->guard('admin')->user(),
            'audits' => $arrAudits,
            'voucher' => $voucher,
            'comments' => $comments
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
        if ($request->has('total_paid') && $request->input('total_paid') != null) {
            $orderData = $request->except('_method', '_token');
        } else {
            $orderData = $request->except('_method', '_token', 'total_paid');
        }
        $orderRepo->updateOrder($orderData);
        return redirect()->route('admin.orders.edit', $orderId);
    }

    public function search(Request $request) {

        $channels = $this->channelRepo->listChannels();
        $statuses = $this->orderStatusRepo->listOrderStatuses();
        $couriers = $this->courierRepo->listCouriers();
        $customers = $this->customerRepo->listCustomers();

        $list = OrderSearch::apply($request);

        $orders = $this->orderRepo->paginateArrayResults($this->transFormOrder($list), 10);

        return view('admin.orders.list', [
            'orders' => $orders,
            'channels' => $channels,
            'statuses' => $statuses,
            'couriers' => $couriers,
            'customers' => $customers
                ]
        );
    }

    /**
     * Show the form for creating a new resource.
     * @param type $channel
     * @return type
     */
    public function create($channel = null) {
        if (!is_null($channel)) {
            $channels = null;
            $channel = $this->channelRepo->listChannels()->where('name', $channel)->first();
            $repo = new ChannelRepository($channel);
            $products = $repo->findProducts()->where('status', 1)->all();
        } else {
            $channels = $this->channelRepo->listChannels();
            $products = $this->productRepo->listProducts()->where('status', 1);
        }

        $customers = $this->customerRepo->listCustomers();
        $couriers = $this->courierRepo->listCouriers();

        return view('admin.orders.create', [
            'selectedChannel' => isset($channel) ? $channel->id : null,
            'channels' => $channels,
            'products' => $products,
            'customers' => $customers,
            'couriers' => $couriers,
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

        $orderRepo = new OrderRepository(new Order);

        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('Waiting Allocation');

        $orderTotal = $request->total;

        $objCourierRate = new CourierRateRepository(new CourierRate);
        $courier = $this->courierRepo->findCourierById($request->courier);

        $country_id = $deliveryAddress->country_id;

        $shipping = $objCourierRate->findShippingMethod($request->total, $courier, $channel, $country_id);

        $shippingCost = 0;

        if (!empty($shipping)) {

            $shippingCost = $shipping->cost;
        }

        $orderTotal += $shippingCost;

        $voucherAmount = 0;

        if (!empty($request->voucher_code)) {
            $voucherCode = $this->voucherCodeRepo->getByVoucherCode($request->voucher_code);

            $voucher_id = $voucherCode->voucher_id;
            $objVoucher = $this->voucherRepo->findVoucherById($voucher_id);

            $voucherAmount = $objVoucher->amount;
        }

        $orderTotal -= $voucherAmount;

        $order = $orderRepo->createOrder([
            'reference' => md5(uniqid(mt_rand(), true) . microtime(true)),
            'courier_id' => $request->courier,
            'customer_id' => $customer->id,
            'voucher_id' => !empty($request->voucher_code) ? $request->voucher_code : null,
            'voucher_code' => $voucherCode->id,
            'address_id' => $deliveryAddress->id,
            'order_status_id' => $os->id,
            'delivery_method' => $shipping,
            'payment' => 'import',
            'discounts' => $voucherAmount,
            'total_shipping' => $shippingCost,
            'total_products' => count($request->products),
            'total' => $orderTotal,
            'total_paid' => 0,
            'channel' => $channel,
            'tax' => 0
                ], new VoucherCodeRepository(new VoucherCode), new CourierRepository(new Courier), new CustomerRepository(new Customer), new AddressRepository(new Address), true
        );

        $orderRepo = new OrderRepository($order);
        $orderRepo->buildOrderLinesForManualOrder($request->products);

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
            'order' => $order,
            'products' => $order->products,
            'customer' => $order->customer,
            'courier' => $order->courier,
            'address' => $this->transformAddress($order->address),
            'status' => $order->orderStatus,
            'channel' => $channel
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
                    return $order;
                })->all();
    }

    /**
     * @param Collection $list
     * @return array
     */
    private function transFormOrderLines(Collection $list) {
        return $list->transform(function (\App\Shop\OrderProducts\OrderProduct $order) {

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

        if (!$newOrder) {
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

        foreach ($request->order as $arrOrder) {

            if ($arrOrder['name'] == 'kondor_product_code[' . $lineId . ']') {
                $productId = $arrOrder['value'];
            }
        }

        if ($productId === null) {
            $arrErrors['errors'][$request->dbID][] = 'unable to find product';
        }

        $arrProducts[0] = [
            'id' => $productId,
            'quantity' => $orderedProduct->quantity
        ];

        try {
            $newOrderRepo = new OrderRepository($newOrder);

            if (!$newOrderRepo->buildOrderLinesForManualOrder($arrProducts)) {
                $strMessage .= 'failed to clone order lines';
                $arrErrors['errors'][$request->dbID][] = $strMessage;
                $blError = true;
            } else {
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

        if ($blError === true) {
            $arrResponse['data']['details']['FAILURES'] = $arrErrors;
        } else {
            $arrResponse['data']['details']['SUCCESS'][$orderId] = ['order updated successfully'];
        }

        echo json_encode($arrResponse);
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

        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('Backorder');

        $items = $this->orderProductRepo->listOrderProducts()->where('status', $os->id);

        $items = $this->orderProductRepo->paginateArrayResults($this->transFormOrderLines($items), 10);

        $channels = $this->channelRepo->listChannels();
        $couriers = $this->courierRepo->listCouriers();

        $arrProducts = $this->productRepo->listProducts();

        return view('admin.orders.backorders', [
            'items' => $items,
            'channels' => $channels,
            'couriers' => $couriers,
            'products' => $arrProducts
                ]
        );
    }

    public function allocations() {

        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('Waiting Allocation');

        $items = $this->orderProductRepo->listOrderProducts()->where('status', $os->id);

        $items = $this->orderProductRepo->paginateArrayResults($this->transFormOrderLines($items), 10);

        $channels = $this->channelRepo->listChannels();
        $couriers = $this->courierRepo->listCouriers();

        $arrProducts = $this->productRepo->listProducts();

        return view('admin.orders.allocations', [
            'items' => $items,
            'channels' => $channels,
            'couriers' => $couriers,
            'products' => $arrProducts
                ]
        );
    }

    public function saveImport(Request $request) {
        $file_path = $request->csv_file->path();
        $line = 0;
        $arrDone = [];
        $arrOrders = [];
        $totalPrice = 0;
        $arrProducts = [];

        if (($handle = fopen($file_path, "r")) !== FALSE) {

            $flag = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $orderTotal = 0;

                if ($flag) {
                    $flag = false;
                    continue;
                }

                $voucherCode = null;

                list(
                        $order['order_id'],
                        $order['channel'],
                        $order['customer'],
                        $order['courier'],
                        $order['voucher_code'],
                        $order['product'],
                        $order['quantity'],
                        $order['price']
                        ) = $data;

                $line++;
                $newOrder = [];

                $csv_errors = Validator::make(
                                $order, (new ImportRequest())->rules()
                        )->errors();


                $channel = $this->channelRepo->findByName($order['channel']);

                if (empty($channel)) {

                    $csv_errors->add('channel', "Channel is invalid.");
                }

                $customer = $order['customer'];

                $customer = $this->customerRepo->searchCustomer($customer);

                if ($customer->isEmpty()) {

                    $csv_errors->add('customer', "Customer is invalid.");
                }

                $customerRepo = new CustomerRepository($customer[0]);
                $deliveryAddress = $customerRepo->findAddresses()->first();

                $country_id = $deliveryAddress->country_id;

                $courier = $this->courierRepo->findByName($order['courier']);

                if (empty($courier)) {

                    $csv_errors->add('courier', "Courier is invalid.");
                }

                $objCourierRate = new CourierRateRepository(new CourierRate);
                $shipping = $objCourierRate->findShippingMethod($orderTotal, $courier, $channel, $country_id);

                $shippingCost = 0;

                if (!empty($shipping)) {

                    $shippingCost = $shipping->cost;
                }

                $orderTotal += $shippingCost;

                $voucherAmount = 0;

                if (!empty($order['voucher_code'])) {
                    $voucherCode = $this->voucherCodeRepo->getByVoucherCode($order['voucher_code']);

                    if (empty($voucherCode)) {

                        $csv_errors->add('voucher_code', "Voucher Code is invalid.");
                    }

                    $voucher_id = $voucherCode->voucher_id;
                    $objVoucher = $this->voucherRepo->findVoucherById($voucher_id);

                    $voucherAmount = $objVoucher->amount;
                }

                $orderTotal -= $voucherAmount;

                $product = $this->productRepo->searchProduct($order['product'])->first();

                $orderTotal += $order['price'];

                if ($csv_errors->any()) {
                    return redirect()->back()
                                    ->withErrors($csv_errors, 'import')
                                    ->with('error_line', $line);
                }



                //$orderRepo = new OrderRepository(new Order);

                $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
                $os = $orderStatusRepo->findByName('Waiting Allocation');


                $voucherCodeId = !empty($voucherCode) ? $voucherCode->id : null;

                if (isset($arrOrders[$order['order_id']]['total']) && !empty($arrOrders[$order['order_id']]['total'])) {
                    $orderTotal += $arrOrders[$order['order_id']]['total'];
                }

                $arrOrders[$order['order_id']] = [
                    'reference' => md5(uniqid(mt_rand(), true) . microtime(true)),
                    'courier_id' => $courier->id,
                    'customer_id' => $customer[0]->id,
                    'voucher_code' => $voucherCodeId,
                    'voucher_id' => !empty($order['voucher_code']) ? $order['voucher_code'] : null,
                    'address_id' => $deliveryAddress->id,
                    'order_status_id' => $os->id,
                    'payment' => 'import',
                    'discounts' => $voucherAmount,
                    'total_shipping' => $shippingCost,
                    'total_products' => 0,
                    'total' => $orderTotal,
                    'total_paid' => 0,
                    'delivery_method' => $shipping,
                    'channel' => $channel,
                    'tax' => 0
                ];


                $arrProducts[$order['order_id']][] = array(
                    'product' => $product->name,
                    'id' => $product->id,
                    'quantity' => $order['quantity']
                );



                $arrDone[] = $order['order_id'];
            }

            fclose($handle);
        }

        foreach ($arrOrders as $orderId => $arrOrder) {

            $order = $this->orderRepo->createOrder($arrOrder, new VoucherCodeRepository(new VoucherCode), new CourierRepository(new Courier), new CustomerRepository(new Customer), new Addressrepository(new Address));

            $orderRepo = new OrderRepository($order);

            $orderRepo->buildOrderLinesForManualOrder($arrProducts[$orderId]);
        }


        request()->session()->flash('message', 'Import successful');
        return redirect()->route('admin.orders.importCsv');
    }

    public function importCsv() {

        return view('admin.orders.importCsv');
    }

    private function addToQueue($data) {

        $config = [
            'factory_class' => \Enqueue\AmqpLib\AmqpConnectionFactory::class,
            'dsn' => null,
            'host' => 'localhost',
            'port' => 15672,
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'options' => [
                'exchange' => [
                    'name' => null,
                    'declare' => true,
                    'type' => \Interop\Amqp\Impl\AmqpTopic::TYPE_DIRECT,
                    'passive' => false,
                    'durable' => true,
                    'auto_delete' => false,
                ],
                'queue' => [
                    'name' => 'mike',
                    'declare' => true,
                    'bind' => true,
                    'passive' => false,
                    'durable' => true,
                    'exclusive' => false,
                    'auto_delete' => false,
                    'arguments' => '[]',
                ],
            ],
            'ssl_params' => [
                'ssl_on' => false,
                'cafile' => null,
                'local_cert' => null,
                'local_key' => null,
                'verify_peer' => true,
                'passphrase' => null,
            ],
        ];

        require_once($_SERVER['DOCUMENT_ROOT'] . '../VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '../VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue.php');

        $connector = new \VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector(new Dispatcher());
        /** @var RabbitMQQueue $queue */
        $queue = $connector->connect($config);

        $queue->setContainer($this->createDummyContainer());
        // we need it to declare exchange\queue on RabbitMQ side.
        $queue->pushRaw('something');
        $queue->getContext()->purgeQueue($queue->getContext()->createQueue('default'));
        $expectedPayload = json_encode($data);
        $queue->pushRaw($expectedPayload);
        sleep(1);
        $job = $queue->pop();

        var_dump($job);
    }

    private function createDummyContainer() {
        $container = new Container();
        $container['log'] = new NullLogger();
        return $container;
    }

}

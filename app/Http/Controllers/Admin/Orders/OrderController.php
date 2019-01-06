<?php

namespace App\Http\Controllers\Admin\Orders;

use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Addresses\Transformations\AddressTransformable;
use App\Shop\Orders\Requests\CreateOrderRequest;
use App\Shop\Couriers\Courier;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Customers\Customer;
use App\Shop\Orders\Requests\ImportRequest;
use App\Shop\Comments\Comment;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Vouchers\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Orders\Order;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Comments\Transformers\CommentTransformer;
use App\Shop\Refunds\Refund;
use App\Shop\Refunds\Repositories\Interfaces\RefundRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Shop\Comments\OrderCommentRepository;
use Illuminate\Support\Collection;
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

    public function __construct(
    OrderRepositoryInterface $orderRepository, CourierRepositoryInterface $courierRepository, AddressRepositoryInterface $addressRepository, CustomerRepositoryInterface $customerRepository, OrderStatusRepositoryInterface $orderStatusRepository, RefundRepositoryInterface $refundRepository, ChannelRepositoryInterface $channelRepository, OrderProductRepositoryInterface $orderProductRepository, ProductRepositoryInterface $productRepository, VoucherRepositoryInterface $voucherRepository
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
        $orderRepo = new OrderRepository($order);
        $items = $this->orderProductRepo->listOrderProducts()->where('order_id', $orderId);

        $voucher = null;

        if (!empty($order->voucher_code)) {

            $voucher = $this->voucherRepo->findVoucherById($order->voucher_code);
        }

        $arrProducts = $this->productRepo->listProducts();

        $arrAudits = $order->audits;

        $list = (new OrderCommentRepository($order))->listComments();

        $comments = $list->map(function (Comment $item) {
                    return $this->transformComment($item);
                })->all();

        $arrRefunds = $this->refundRepo->getRefundsForOrderByLineId($order);

        return view('admin.orders.edit', [
            'statuses' => $this->orderStatusRepo->listOrderStatuses(),
            'products' => $arrProducts,
            'order' => $order,
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

        $list = $this->orderRepo->searchOrder($request);

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

        return view('admin.orders.create', [
            'selectedChannel' => isset($channel) ? $channel->id : null,
            'channels' => $channels,
            'products' => $products,
            'customers' => $customers
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

        $order = $orderRepo->createOrder([
            'reference' => md5(uniqid(mt_rand(), true) . microtime(true)),
            'courier_id' => 1,
            'customer_id' => $customer->id,
            'voucher_code' => null,
            'address_id' => $deliveryAddress->id,
            'order_status_id' => $os->id,
            'payment' => 'import',
            'discounts' => 0,
            'shipping' => 0,
            'total_products' => 1,
            'total' => $request->total,
            'total_paid' => $request->total,
            'channel' => $channel,
            'tax' => 0
                ], true);

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

        $channel = $this->channelRepo->findByName(env('CHANNEL'));
        $order = $this->orderRepo->findOrderById($request->order_id);

        $newOrder = $this->orderRepo->cloneOrder($order, $channel);
        $blError = false;

        if (!$newOrder) {
            $strMessage = 'failed to create rma order';

            $data = [
                'content' => $strMessage,
                'user_id' => auth()->guard('admin')->user()->id
            ];

            $postRepo = new OrderCommentRepository($order);
            $postRepo->createComment($data);
        }

        $orderId = $newOrder->id;
        $strMessage = $orderId . 'was created as RMA';

        if (!$this->orderProductRepo->cloneOrderLines($order, $newOrder, $request->lineIds, true)) {
            $strMessage .= 'failed to clone order lines';
            $blError = true;
        }

        $data = [
            'content' => $strMessage,
            'user_id' => auth()->guard('admin')->user()->id
        ];

        $order->update(['customer_ref' => 'RMA_' . md5(uniqid(mt_rand(), true) . microtime(true))]);

        $postRepo = new OrderCommentRepository($order);
        $postRepo->createComment($data);

        if ($blError === true) {
            return response()->json(['error' => $strMessage], 404); // Status code here
        }
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

        return view('admin.orders.backorders', [
            'items' => $items,
            'channels' => $channels
                ]
        );
    }

    public function allocations() {

        $orderStatusRepo = new OrderStatusRepository(new OrderStatus);
        $os = $orderStatusRepo->findByName('Waiting Allocation');

        $items = $this->orderProductRepo->listOrderProducts()->where('status', $os->id);

        $items = $this->orderProductRepo->paginateArrayResults($this->transFormOrderLines($items), 10);

        $channels = $this->channelRepo->listChannels();

        return view('admin.orders.allocations', [
            'items' => $items,
            'channels' => $channels
                ]
        );
    }

    public function saveImport(Request $request) {
        $file_path = $request->csv_file->path();
        $line = 0;

        if (($handle = fopen($file_path, "r")) !== FALSE) {

            $flag = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                if ($flag) {
                    $flag = false;
                    continue;
                }
                
                $line++;
                $order = [];

                list(
                        $order['channel'],
                        $order['customer_id'],
                        $order['voucher_code'],
                        $order['shipping'],
                        $order['total']
                        ) = $data;

                $csv_errors = Validator::make(
                                $order, (new ImportRequest())->rules()
                        )->errors();

                // Add any additional validation here.
                // For example, validates that only certain years allowed
                $allowed_years = [2013, 2015];

//                if (!in_array($book['year'], $allowed_years)) {
//                    $csv_errors->add('year', "Year must be either 2013 or 2015.");
//                }
//

                if ($csv_errors->any()) {
                    return redirect()->back()
                                    ->withErrors($csv_errors, 'import')
                                    ->with('error_line', $line);
                }
            }

            fclose($handle);
        }

        // Dispatch job to store the data in database
        //dispatch(new StoreBooks($file_path));
    }

    public function importCsv() {

        return view('admin.orders.importCsv');
    }

}

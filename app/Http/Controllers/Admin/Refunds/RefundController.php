<?php

namespace App\Http\Controllers\Admin\Refunds;

use App\Shop\Refunds\Refund;
use App\Shop\Refunds\Repositories\RefundRepository;
use App\Shop\Comments\OrderCommentRepository;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use App\Shop\PaymentMethods\Paypal\Repositories\PayPalExpressCheckoutRepository;
use App\Shop\PaymentMethods\Stripe\StripeRepository;
use App\Shop\Refunds\Repositories\Interfaces\RefundRepositoryInterface;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\Refunds\Requests\CreateRefundRequest;
use App\Shop\Refunds\Requests\UpdateRefundRequest;
use App\Shop\Refunds\Transformations\RefundTransformable;
use App\Shop\Orders\Order;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\Channels\Channel;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\OrderProducts\OrderProduct;
use App\Shop\Channels\Repositories\ChannelRepository;
use Illuminate\Http\Request;
use App\Traits\OrderCommentTrait;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Http\Controllers\Controller;

class RefundController extends Controller {

    use RefundTransformable,
        OrderCommentTrait;

    /* @param RefundRepositoryInterface $refundRepo */

    private $refundRepo;

    /* @param OrderRepositoryInterface $orderRepo */
    private $orderRepo;

    /**
     * @var OrderStatusRepositoryInterface
     */
    private $orderStatusRepo;

    /**
     * @var OrderProductRepositoryInterface
     */
    private $orderProductRepo;

    /**
     * 
     * @param RefundRepositoryInterface $refundRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
    RefundRepositoryInterface $refundRepository, OrderRepositoryInterface $orderRepository, OrderStatusRepositoryInterface $orderStatusRepository, OrderProductRepositoryInterface $orderProductRepository
    ) {
        $this->refundRepo = $refundRepository;
        $this->orderRepo = $orderRepository;
        $this->orderStatusRepo = $orderStatusRepository;
        $this->orderProductRepo = $orderProductRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $list = $this->refundRepo->listRefund('created_at', 'desc');

        if (request()->has('q'))
        {
            $list = $this->refundRepo->searchRefund(request()->input('q'));
        }

        $refunds = $list->map(function (Refund $refund) {
                    return $this->transformRefund($refund);
                })->all();

        return view('admin.refunds.list', ['refunds' => $this->refundRepo->paginateArrayResults($refunds)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $order = $this->orderRepo->findOrderById(1);

        return view('admin.refunds.create', [
            'order' => $order,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateAddressRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRefundRequest $request) {

        $list = $this->orderProductRepo->listOrderProducts()->where('order_id', $request->order_id)->where('product_id', $request->line_id)->first();

        $data = $request->except('_token', '_method');

        $data['date_refunded'] = date('Y-m-d'); //add request

        $this->orderProductRepo->updateOrderProduct(['status' => 8], $list->id);

        $this->refundRepo->createRefund($data);

        $request->session()->flash('message', 'Creation successful');
        return redirect()->route('admin.refunds.index');
    }

    /**
     * 
     * @param CreateRefundRequest $request
     */
    public function doRefund(Request $request) {

        $order = (new OrderRepository(new Order))->findOrderById($request->order_id);
        $orderProducts = (new OrderProductRepository(new OrderProduct))->listOrderProducts()->where('order_id', $request->order_id);
        $channel = (new ChannelRepository(new Channel))->findChannelById($order->channel);
        $blError = false;
        $arrSuccesses = [];
        $arrFailures = [];


        if ($order->total_paid <= 0)
        {
            $arrFailures[$request->order_id][] = 'The order has not yet been paid';
            return response()->json(['http_code' => 400, 'FAILURES' => $arrFailures]);
        }

        $objCustomerRepository = new CustomerRepository(new Customer);

        $customer = $objCustomerRepository->findCustomerById($order->customer_id);

        $refundAmount = $this->refundRepo->refundLinesForOrder($request, $order, $channel, $orderProducts);

        if (!$refundAmount)
        {
            return response()->json(['error' => 'failed to update order lines'], 404); // Status code here
        }

        $refundAmount += $order->total_shipping;

        $totalPaid = $order->total_paid - $refundAmount;
        $totalRefunded = $order->amount_refunded + $refundAmount;

        try {
            $orderRepo = new OrderRepository($order);

            $orderRepo->updateOrder(
                    [
                        //'total_paid'      => $totalPaid,
                        'amount_refunded' => $totalRefunded                    ]
            );

        } catch (\Exception $e) {
            $strMessage = "Unable to refund order {$e->getMessage()}";
            $arrFailures[$request->order_id][] = $e->getMessage();
            $this->saveNewComment($order, $strMessage);
            return response()->json(['http_code' => 400, 'FAILURES' => $arrFailures]);
        }

        if (!$this->authorizePayment($order, $refundAmount, $customer))
        {

            $strMessage = "Order was refunded but we failed to authorize payment";
            $arrFailures[$request->order_id][] = $strMessage;
            $this->saveNewComment($order, $strMessage);
            return response()->json(['http_code' => 400, 'FAILURES' => $arrFailures]);
        }

        if ($customer->customer_type == 'credit')
        {

            $objCustomerRepository->addCredit($customer->id, 10);
            $this->saveNewComment($order, 'customer has been credited for refund');
        }

        $http_code = $blError === true ? 400 : 200;
        return response()->json(['http_code' => $http_code, 'SUCCESS' => $arrSuccesses, 'FAILURES' => $arrFailures]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id) {
        return view('admin.refunds.show', ['refund' => $this->refundRepo->findRefundById($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {
        $refund = $this->refundRepo->findRefundById($id);

        return view('admin.refunds.edit', [
            'refund' => $refund,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateAddressRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRefundRequest $request, $id) {
        $refund = $this->refundRepo->findRefundById($id);

        $update = new RefundRepository($refund);
        $update->updateRefund($request->except('_method', '_token'));

        $request->session()->flash('message', 'Update successful');
        return redirect()->route('admin.refunds.edit', $id);
    }

    /**
     * 
     * @param Order $order
     * @param type $refundAmount
     * @param Customer $customer
     * @return boolean
     */
    private function authorizePayment(Order $order, $refundAmount, Customer $customer) {

        switch ($order->payment)
        {
            case 'paypal':

                if (!(new PayPalExpressCheckoutRepository())->doRefund($order, $refundAmount))
                {

                    return response()->json(['error' => 'failed to authorize'], 404); // Status code here
                }
                break;

            case 'stripe':
                if (!(new StripeRepository($customer))->doRefund($order))
                {
                    return response()->json(['error' => 'failed to authorize'], 404); // Status code here
                }
                break;
        }
        
        $strMessage = $refundAmount . 'was successfully refunded using ' . $order->payment;
        $this->saveNewComment($order, $strMessage);

        return true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $refund = $this->refundRepo->findRefundById($id);
        $delete = new RefundRepository($refund);
        $delete->deleteRefund();

        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.refunds.index');
    }

}

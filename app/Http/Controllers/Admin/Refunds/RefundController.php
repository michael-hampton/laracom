<?php

namespace App\Http\Controllers\Admin\Refunds;

use App\Shop\Refunds\Refund;
use App\Shop\Refunds\Repositories\RefundRepository;
use App\Shop\PaymentMethods\Paypal\Repositories\PayPalExpressCheckoutRepository;
use App\Shop\PaymentMethods\Stripe\StripeRepository;
use App\Shop\Refunds\Repositories\Interfaces\RefundRepositoryInterface;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\Refunds\Requests\CreateRefundRequest;
use App\Shop\Refunds\Requests\UpdateRefundRequest;
use App\Shop\Refunds\Transformations\RefundTransformable;
use App\Shop\Orders\Order;
use App\Shop\Customers\Customer;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Http\Controllers\Controller;

class RefundController extends Controller {

    use RefundTransformable;

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

        if (request()->has('q')) {
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

        $refundAmount = 0;
        $order = $this->orderRepo->findOrderById($request->order_id);
        
        foreach($request->lineIds as $lineId) {
            $orderProduct = $this->orderProductRepo->findOrderProductById($lineId);
        
            $refundAmount += $orderProduct->product_price;
            
            $orderProductRepo = new OrderProductRepository($orderProduct);

            $data = [];
            $data['date_refunded'] = date('Y-m-d'); //add request
            $data['quantity'] = $orderProduct->quantity;
            $data['lineId'] = $lineId;
            $data['orderId'] = $request->order_id;
            $data['status'] = $request->status;
            $data['amount'] = $orderProduct->product_price;
            
            $this->refundRepo->createRefund($data);

            $orderProductRepo->updateOrderProduct(
                    [
                        'status' => $request->status
                    ], $request->lineId
            );
        }
        
        
        $customer = (new \App\Shop\Customers\Repositories\CustomerRepository(new Customer()))->findCustomerById($order->customer_id);

        switch ($order->payment) {
            case 'paypal':

                if (!(new PayPalExpressCheckoutRepository())->doRefund($order, $refundAmount)) {

                    die('cant do refund');
                }
                break;

            case 'stripe':
                if (!(new StripeRepository($customer))->doRefund($order, $refundAmount)) {
                    die('Cant do refund');
                }
                break;
        }

        $request->session()->flash('message', 'Creation successful');
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

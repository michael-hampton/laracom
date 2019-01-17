php
namespace App\Http\Controllers\Admin\Returns;
use App\Shop\Returns\Return;
use App\Shop\Returns\Repositories\ReturnRepository;
use App\Shop\Comments\OrderCommentRepository;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use App\Shop\PaymentMethods\Paypal\Repositories\PayPalExpressCheckoutRepository;
use App\Shop\PaymentMethods\Stripe\StripeRepository;
use App\Shop\Refunds\Repositories\Interfaces\RefundRepositoryInterface;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\Returns\Requests\CreateReturnRequest;
use App\Shop\Returns\Requests\UpdateReturnRequest;
use App\Shop\Returns\Transformations\ReturnTransformable;
use App\Shop\Orders\Order;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\Channels\Channel;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\OrderProducts\OrderProduct;
use App\Shop\Channels\Repositories\ChannelRepository;
use Illuminate\Http\Request;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\OrderStatuses\Repositories\Interfaces\OrderStatusRepositoryInterface;
use App\Http\Controllers\Controller;
class ReturnController extends Controller {
    use ReturnTransformable;
    /* @param ReturnRepositoryInterface $refundRepo */
    private $returnRepo;
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
     * @param ReturnRepositoryInterface $refundRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
    ReturnRepositoryInterface $refundRepository, OrderRepositoryInterface $orderRepository, OrderStatusRepositoryInterface $orderStatusRepository, OrderProductRepositoryInterface $orderProductRepository
    ) {
        $this->returnRepo = $returnRepository;
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
        $list = $this->returnRepo->listReturn('created_at', 'desc');
        if (request()->has('q')) {
            $list = $this->returnRepo->searchReturn(request()->input('q'));
        }
        $returns = $list->map(function (Return $return) {
                    return $this->transformReturn($return);
                })->all();
        return view('admin.returns.list', ['returns' => $this->returnRepo->paginateArrayResults($returns)]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $order = $this->orderRepo->findOrderById(1);
        return view('admin.returns.create', [
            'order' => $order,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateReturnRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateReturnRequest $request) {
        $list = $this->orderProductRepo->listOrderProducts()->where('order_id', $request->order_id)->where('product_id', $request->line_id)->first();
        $data = $request->except('_token', '_method');
        $data['date_returned'] = date('Y-m-d'); //add request
        $this->orderProductRepo->updateOrderProduct(['status' => 8], $list->id);
        $this->returnRepo->createReturn($data);
        $request->session()->flash('message', 'Creation successful');
        return redirect()->route('admin.returns.index');
    }
    /**
     * 
     * @param CreateReturnRequest $request
     */
    public function doRefund(Request $request) {
        $order = (new OrderRepository(new Order))->findOrderById($request->order_id);
        $orderProducts = (new OrderProductRepository(new OrderProduct))->listOrderProducts()->where('order_id', $request->order_id);
        $channel = (new ChannelRepository(new Channel))->findChannelById($order->channel);
        $blError = false;
        $arrSuccesses = [];
        $arrFailures = [];
        if ($order->total_paid <= 0) {
            $arrFailures[$request->order_id][] = 'The order has not yet been paid';
            echo json_encode(['http_code' => 400, 'SUCCESS' => $arrSuccesses, 'FAILURES' => $arrFailures]);
            die;
        }
        $objCustomerRepository = new CustomerRepository(new Customer);
        $customer = $objCustomerRepository->findCustomerById($order->customer_id);
        $refundAmount = $this->refundRepo->refundLinesForOrder($request, $order, $channel, $orderProducts);
        if (!$refundAmount) {
            return response()->json(['error' => 'failed to update order lines'], 404); // Status code here
        }
        $totalPaid = $order->total_paid - $refundAmount;
        $refundAmount = $order->amount_refunded + $refundAmount;
        try {
            $orderRepo = new OrderRepository($order);
            $orderRepo->updateOrder(
                    [
                        'total_paid' => $totalPaid,
                        'amount_refunded' => $refundAmount,
                        'order_status_id' => $order->status
                    ]
            );
            $strMessage = "Order has been refunded";
        } catch (\Exception $e) {
            $strMessage = "Unable to refund order {$e->getMessage()}";
            $blError = true;
            $arrFailures[$request->order_id][] = $e->getMessage();
        }
        if (!$this->authorizePayment($order, $customer)) {
            $strMessage = "Order was refunded but we failed to authorize payment";
            $arrFailures[$request->order_id][] = $strMessage;
            $blError = true;
        }
        if ($customer->customer_type == 'credit') {
            $objCustomerRepository->addCredit($customer->id, 10);
        }
        $data = [
            'content' => $strMessage,
            'user_id' => auth()->guard('admin')->user()->id
        ];
        $postRepo = new OrderCommentRepository($order);
        $postRepo->createComment($data);
        $http_code = $blError === true ? 400 : 200;
        echo json_encode(['http_code' => $http_code, 'SUCCESS' => $arrSuccesses, 'FAILURES' => $arrFailures]);
        die;
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id) {
        return view('admin.returns.show', ['return' => $this->returnRepo->findReturnById($id)]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {
        $return = $this->returnRepo->findReturnById($id);
        return view('admin.returns.edit', [
            'return' => $return,
        ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateReturnRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReturnRequest $request, $id) {
        $refund = $this->returnRepo->findReturnById($id);
        $update = new ReturnRepository($refund);
        $update->updateReturn($request->except('_method', '_token'));
        $request->session()->flash('message', 'Update successful');
        return redirect()->route('admin.returns.edit', $id);
    }
    /**
     * 
     * @param Order $order
     * @param Customer $customer
     * @return boolean
     */
    private function authorizePayment(Order $order, Customer $customer) {
        return true;
        switch ($order->payment) {
            case 'paypal':
                if (!(new PayPalExpressCheckoutRepository())->doRefund($order)) {
                    return response()->json(['error' => 'failed to authorize'], 404); // Status code here
                }
                break;
            case 'stripe':
                if (!(new StripeRepository($customer))->doRefund($order)) {
                    return response()->json(['error' => 'failed to authorize'], 404); // Status code here
                }
                break;
        }
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

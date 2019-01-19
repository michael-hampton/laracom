<?php
namespace App\Http\Controllers\Front;

use App\Shop\Returns\Returns;
use App\Shop\Returns\Repositories\Interfaces\ReturnLineRepositoryInterface;
use App\Shop\Returns\Repositories\ReturnRepository;
use App\Shop\Comments\OrderCommentRepository;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use App\Shop\Returns\Repositories\Interfaces\ReturnRepositoryInterface;
use App\Shop\OrderProducts\Repositories\Interfaces\OrderProductRepositoryInterface;
use App\Shop\Returns\Requests\CreateReturnRequest;
use App\Shop\Returns\Requests\UpdateReturnRequest;
use App\Shop\Returns\Transformations\ReturnTransformable;
use App\Shop\Returns\Repositories\ReturnLineRepository;
use App\Shop\Returns\ReturnLine;
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

class CustomerReturnController extends Controller
{
    use ReturnTransformable;
    
    /* @param ReturnRepositoryInterface $refundRepo */
    private $returnRepo;
    
    /* @param ReturnRepositoryInterface $refundRepo */
    private $returnLineRepo;
    
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
    ReturnRepositoryInterface $returnRepository, ReturnLineRepositoryInterface $returnLineRepository, OrderRepositoryInterface $orderRepository, OrderStatusRepositoryInterface $orderStatusRepository, OrderProductRepositoryInterface $orderProductRepository
    ) {
        $this->returnRepo = $returnRepository;
        $this->returnLineRepo = $returnLineRepository;
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
        $returns = $list->map(function (Returns $return) {
                    return $this->transformReturn($return);
                })->all();
        return view('admin.returns.list', ['returns' => $this->returnRepo->paginateArrayResults($returns)]);
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($orderId) {
        $order = $this->orderRepo->findOrderById($orderId);
        $orderRepo = new OrderRepository($order);
        $items = $orderRepo->listOrderedProducts();
        $status = (new \App\Shop\Returns\ReturnStatus())->get();
        return view('admin.returns.create', [
            'order' => $order,
            'items' => $items,
            'reasons' => explode(',', env('RETURN_REASON')),
            'statuses' => $status,
            'conditions' => explode(',', env('RETURN_CONDITIONS')),
            'resolutions' => explode(',', env('RETURN_RESOLUTIONS'))
        ]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateReturnRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateReturnRequest $request) {
        $data = $request->except('_token', '_method', 'lines');
        $return = $this->returnRepo->createReturn($data);
        foreach ($request->lines as $line) {
            $this->returnLineRepo->createReturnLine($line, $return);
        }
        $request->session()->flash('message', 'Creation successful');
        return redirect()->route('admin.returns.index');
    }
}

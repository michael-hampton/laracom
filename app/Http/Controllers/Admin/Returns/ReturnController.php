<?php

namespace App\Http\Controllers\Admin\Returns;

use App\Shop\Returns\Returns;
use App\Shop\Returns\Repositories\Interfaces\ReturnLineRepositoryInterface;
use App\Shop\Returns\Repositories\ReturnRepository;
use App\Shop\Comments\OrderCommentRepository;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
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

class ReturnController extends Controller {

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
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

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
        ReturnRepositoryInterface $returnRepository, 
        ReturnLineRepositoryInterface $returnLineRepository, 
        OrderRepositoryInterface $orderRepository, 
        OrderStatusRepositoryInterface $orderStatusRepository,
        OrderProductRepositoryInterface $orderProductRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->returnRepo = $returnRepository;
        $this->customerRepo = $customerRepository;
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
        
        $customers = $this->customerRepo->listCustomers('created_at', 'desc')->keyBy('id');
        
        return view('admin.returns.list', [
            'returns' => $this->returnRepo->paginateArrayResults($returns),
            'customers' => $customers
        ]);
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
        $customers = $this->customerRepo->listCustomers('created_at', 'desc');
        
        return view('admin.returns.create', [
            'order' => $order,
            'items' => $items,
            'reasons' => explode(',', env('RETURN_REASON')),
            'statuses' => $status,
            'customers' => $customers,
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
        $order = $this->orderRepo->findOrderById($return->order_id);
        $orderRepo = new OrderRepository($order);
        $items = $orderRepo->listOrderedProducts()->keyBy('id');
        $returnLines = $this->returnLineRepo->listReturnLine()->where('return_id', $return->id);
        $status = (new \App\Shop\Returns\ReturnStatus())->get();
        $customers = $this->customerRepo->listCustomers('created_at', 'desc');
        
        return view('admin.returns.edit', [
            'return' => $return,
            'order' => $order,
            'customers' => $customers,
            'items' => $items,
            'returnLines' => $returnLines,
            'reasons' => explode(',', env('RETURN_REASON')),
            'statuses' => $status,
            'conditions' => explode(',', env('RETURN_CONDITIONS')),
            'resolutions' => explode(',', env('RETURN_RESOLUTIONS'))
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
        $return = $this->returnRepo->findReturnById($id);
        $update = new ReturnRepository($return);
        $update->updateReturn($request->except('_method', '_token', 'lines'));

        foreach ($request->lines as $returnLineId => $line) {

            $return = $this->returnLineRepo->findReturnLineById($returnLineId);
            $update = new ReturnLineRepository($return);
            $update->updateReturnLine($line);
        }

        $request->session()->flash('message', 'Update successful');
        return redirect()->route('admin.returns.edit', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {

        $return = $this->returnRepo->findReturnById($id);
        $delete = new ReturnRepository($return);
        $delete->deleteReturn();
        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.returns.index');
    }

}

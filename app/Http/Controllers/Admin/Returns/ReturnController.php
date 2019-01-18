<?php
namespace App\Http\Controllers\Admin\Returns;
use App\Shop\Returns\Return;
use App\Shop\Returns\Repositories\ReturnRepository;
use App\Shop\Comments\OrderCommentRepository;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use App\Shop\Returns\Repositories\Interfaces\ReturnRepositoryInterface;
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
    ReturnRepositoryInterface $returnRepository, OrderRepositoryInterface $orderRepository, OrderStatusRepositoryInterface $orderStatusRepository, OrderProductRepositoryInterface $orderProductRepository
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
        
        $data = $request->except('_token', '_method');
        
        $this->returnRepo->createReturn($data);
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
        $return = $this->returnRepo->findReturnById($id);
        $update = new ReturnRepository($return);
        $update->updateReturn($request->except('_method', '_token'));
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
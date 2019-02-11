<?php

namespace App\Http\Controllers\Admin\Channels;

use App\Shop\Channels\Channel;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Employees\Repositories\Interfaces\EmployeeRepositoryInterface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Channels\Repositories\ChannelWarehouseRepository;
use App\Shop\Channels\ChannelWarehouse;
use Illuminate\Support\Facades\Auth;
use App\Shop\Employees\Repositories\EmployeeRepository;
use App\Shop\Channels\Requests\CreateChannelRequest;
use App\Shop\Channels\Requests\UpdateChannelRequest;
use App\Http\Controllers\Controller;
use App\Shop\Channels\Transformations\ChannelTransformable;
use App\Shop\Tools\UploadableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use App\Shop\Channels\Repositories\ChannelTemplateRepository;
use App\Shop\ChannelPrices\Repositories\ChannelPriceRepository;
use App\Shop\Channels\Repositories\ChannelPaymentProviderRepository;
use App\Shop\Channels\ChannelPaymentProvider;
use App\Shop\Channels\ChannelTemplate;
use App\Shop\Channels\Repositories\WarehouseRepository;
use App\Shop\Channels\Warehouse;

class ChannelController extends Controller {

    use ChannelTransformable,
        UploadableTrait;

    /**
     * @var ProductRepositoryInterface
     */
    private $channelRepo;

    /**
     * @var EmployeeInterface
     */
    private $employeeRepo;

    /**
     * @var EmployeeInterface
     */
    private $productRepo;

    /**
     * 
     * @param ChannelRepositoryInterface $channelRepository
     * @param EmployeeRepositoryInterface $employeeRepository
     * @param \App\Http\Controllers\Admin\Channels\ProductRepositoryInterface $productRepository
     */
    public function __construct(
    ChannelRepositoryInterface $channelRepository, EmployeeRepositoryInterface $employeeRepository, ProductRepositoryInterface $productRepository
    ) {
        $this->employeeRepo = $employeeRepository;
        $this->channelRepo = $channelRepository;
        $this->productRepo = $productRepository;

        $this->middleware(['permission:create-channel, guard:admin'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:update-channel, guard:admin'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete-channel, guard:admin'], ['only' => ['destroy']]);
        $this->middleware(['permission:view-channel, guard:admin'], ['only' => ['index', 'show', 'export']]);
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function addProductToChannel(Request $request) {

        try {
            $channel = $this->channelRepo->findChannelById($request->channel);
            $channelWarehouses = (new WarehouseRepository(new Warehouse))->getWarehousesForChannel($channel)->keyBy('id');
            $product = $this->productRepo->findProductById($request->product);
            $productWarehouse = $product->warehouse;
            $warehouses_on = !empty(env('ALLOW_WAREHOUSES')) ? true : false;

            if ($warehouses_on === true && !empty($productWarehouse) && !isset($channelWarehouses[$productWarehouse]))
            {

                return response()->json(['http_code' => 400, 'errors' => ['The product is in a warehouse which the channel doesnt have access to.']]);
            }

            $channelPriceRepo = new ChannelPriceRepository(new \App\Shop\ChannelPrices\ChannelPrice);

            $channelPriceRepo->create([
                'channel_id' => $request->channel,
                'warehouse'  => $productWarehouse,
                'product_id' => $request->product,
                'price'      => $request->price
            ]);
        } catch (Exception $ex) {
            return response()->json(['http_code' => 400, 'errors' => [$ex->getMessage()]]);
        }


        return response()->json(['http_code' => 200]);
    }

    /**
     * 
     * @param type $channelId
     */
    public function saveChannelTemplate(Request $request) {

        foreach ($request->templates as $templateId => $template)
        {

            $value = array_values($template);
            $key = array_keys($template);

            try {
                (new ChannelTemplateRepository(new ChannelTemplate))->updateOrCreate(
                        [
                    'channel_id'  => $request->channel,
                    'section_id'  => $templateId,
                    'title'       => $key[0],
                    'description' => $value[0]
                        ], [
                    'channel_id' => $request->channel,
                    'section_id' => $templateId
                        ]
                );
            } catch (Exception $ex) {
                return response()->json(['http_code' => 400, 'errors' => [$ex->getMessage()]]);
            }
        }

        return response()->json(['http_code' => 200]);
    }

    /**
     * 
     * @param type $channelId
     */
    public function addChannelProvider(Request $request) {

        try {
            (new ChannelPaymentProviderRepository(new ChannelPaymentProvider))->create([
                'channel_id'          => $request->channel,
                'payment_provider_id' => $request->provider
                    ]
            );
        } catch (Exception $ex) {
            return response()->json(['http_code' => 400, 'errors' => [$ex->getMessage()]]);
        }

        return response()->json(['http_code' => 200]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($all = false) {

        $currentAuthUserId = Auth::guard('admin')->user()->id;

        if ($all === true || $currentAuthUserId === 1)
        {
            $list = $this->channelRepo->listChannels('id');

            if (request()->has('q') && request()->input('q') != '')
            {
                $list = $this->channelRepo->searchChannel(request()->input('q'));
            }

            $channels = $list->map(function (Channel $item) {
                        return $this->transformChannel($item);
                    })->all();

            return view('admin.channels.list', [
                'channels' => $this->channelRepo->paginateArrayResults($channels, 8)
            ]);
        }

        $employee = $this->employeeRepo->findEmployeeById($currentAuthUserId);

        $employeeRepo = new EmployeeRepository($employee);
        $list = $employeeRepo->findEmployeeChannels();

        $employeeStores = $list->map(function (Channel $item) {
                    return $this->transformChannel($item);
                })->all();

        return view('admin.channels.list', [
            'channels' => $this->employeeRepo->paginateArrayResults($employeeStores, 8)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        return view('admin.channels.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateChannelRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        $data = $request->except('_token', '_method');
        //$data['slug'] = str_slug($request->input('name'));

        if ($request->hasFile('cover') && $request->file('cover') instanceof UploadedFile)
        {
            $data['cover'] = $this->channelRepo->saveCoverImage($request->file('cover'));
        }

        $validator = Validator::make($data, (new CreateChannelRequest())->rules());
        // Validate the input and return correct response

        if ($validator->fails())
        {
            return response()->json(['http_code' => 400, 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $this->channelRepo->createChannel($data);

        return response()->json(['http_code' => 200]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id) {
        $channel = $this->channelRepo->findChannelById($id);

        return view('admin.channels.show', [
            'channel' => $channel
        ]);
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    public function deleteProvider($id) {

        $paymentProvider = (new \App\Shop\Channels\PaymentProvider())->where('id', $id)->first();

        (new ChannelPaymentProviderRepository(new ChannelPaymentProvider))->deleteChannelFromProvider($paymentProvider);

        return response()->json(['http_code' => 200]);
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    public function deleteProduct($product_id, $channel_id) {

        $channel = $this->channelRepo->findChannelById($channel_id);
        $objProduct = $this->productRepo->findProductById($product_id);
        $channelProduct = (new ChannelPriceRepository(new \App\Shop\ChannelPrices\ChannelPrice))->getChannelProduct($objProduct, $channel);
        $channelProduct->delete();
        return response()->json(['http_code' => 200]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {

        $channel = $this->channelRepo->findChannelById($id);
        $arrProducts = (new ChannelPriceRepository(new \App\Shop\ChannelPrices\ChannelPrice))->getAvailiableProducts($channel);

        $arrTemplates = (new ChannelTemplateRepository(new ChannelTemplate))->getTemplatesForChannel($channel);
        $arrChannels = $this->channelRepo->listChannels();
        $arrProviders = (new ChannelPaymentProviderRepository(new ChannelPaymentProvider))->getProvidersForChannel($channel);
        $arrAssignedProducts = (new ChannelPriceRepository(new \App\Shop\ChannelPrices\ChannelPrice))->getAssignedProductsForChannel($channel);
        $arrPaymentProviders = (new \App\Shop\Channels\PaymentProvider)->get();
        $objWarehouseRepository = (new WarehouseRepository(new Warehouse));
        $arrWarehouses = $objWarehouseRepository->listWarehouses('name', 'asc');
        $arrAssignedWarehouses = (new ChannelWarehouseRepository(new ChannelWarehouse))->getWarehousesForChannel($channel)->keyBy('warehouse_id');

        return view('admin.channels.edit', [
            'assigned_warehouses' => $arrAssignedWarehouses,
            'templates'           => $arrTemplates,
            'products'            => $arrProducts,
            'warehouses'          => $arrWarehouses,
            'channel'             => $channel,
            'channels'            => $arrChannels,
            'arrProviders'        => $arrPaymentProviders,
            'providers'           => $arrProviders,
            'assigned_products'   => $arrAssignedProducts
        ]);
    }

    /**
     * 
     * @param Request $request
     */
    public function addChannelToWarehouse(Request $request) {
        $channelWarehouseRepo = new ChannelWarehouseRepository(new ChannelWarehouse);

        $channelWarehouseRepo->create([
            'channel_id'   => $request->channel,
            'warehouse_id' => $request->warehouse,
        ]);

        return response()->json(['http_code' => 200]);
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    public function deleteWarehouse($id) {

        (new ChannelWarehouseRepository(new ChannelWarehouse))
                ->findChannelWarehouseById($id)
                ->delete();

        return response()->json(['http_code' => 200]);
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function update(Request $request, $id) {
        $channel = $this->channelRepo->findChannelById($id);
        $channelRepo = new ChannelRepository($channel);

        $data = $request->except('_token', '_method', 'id');

        if ($request->hasFile('cover') && $request->file('cover') instanceof UploadedFile)
        {

            $data['cover'] = $channelRepo->saveCoverImage($request->file('cover'));
        }

        $validator = Validator::make($data, (new UpdateChannelRequest())->rules());

        // Validate the input and return correct response
        if ($validator->fails())
        {
            return response()->json(['http_code' => 400, 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $channelRepo->updateChannel($data);

        return response()->json(['http_code' => 200, 'message' => 'Channel has been updated successfully']);
    }

    /**
     * 
     * @param Request $request
     * @return type
     */
    public function updateChannel(Request $request) {

        $id = $request->id;

        $this->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {

        $this->channelRepo->delete($id);

        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.channels.index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeImage(Request $request) {
        $this->channelRepo->deleteFile($request->only('channel', 'image'), 'uploads');
        request()->session()->flash('message', 'Image delete successful');
        return redirect()->back();
    }

    /**
     * 
     * @param Request $request
     */
    public function saveChannelAttribute(Request $request) {

        $data = [$request->id => $request->value];

        $channel = $this->channelRepo->findChannelById($request->channelId);
        $channelRepo = new ChannelRepository($channel);

        $channelRepo->updateChannel($data);
    }

}

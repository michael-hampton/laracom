<?php

namespace App\Http\Controllers\Admin\Channels;

use App\Shop\Channels\Channel;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Employees\Repositories\Interfaces\EmployeeRepositoryInterface;
use App\Shop\Channels\Repositories\ChannelRepository;
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
     * ProductController constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
    ChannelRepositoryInterface $channelRepository, EmployeeRepositoryInterface $employeeRepository
    ) {
        $this->employeeRepo = $employeeRepository;
        $this->channelRepo = $channelRepository;
    }

    public function addProductToChannel(Request $request) {

        $channelPriceRepo = new ChannelPriceRepository(new \App\Shop\ChannelPrices\ChannelPrice);

        $channelPriceRepo->create([
            'channel_id' => $request->channel,
            'product_id' => $request->product,
            'price' => $request->price
        ]);

        echo json_encode(array(
            'http_code' => 200,
        ));
    }

    /**
     * 
     * @param type $channelId
     */
    public function saveChannelTemplate(Request $request) {

        foreach ($request->templates as $templateId => $template) {

            $value = array_values($template);
            $key = array_keys($template);

            (new ChannelTemplateRepository(new ChannelTemplate))->updateOrCreate(
                    [
                'channel_id' => $request->channel,
                'section_id' => $templateId,
                'title' => $key[0],
                'description' => $value[0]
                    ], [
                'channel_id' => $request->channel,
                'section_id' => $templateId
                    ]
            );
        }

        echo json_encode(array(
            'http_code' => 200,
        ));
    }

    /**
     * 
     * @param type $channelId
     */
    public function addChannelProvider(Request $request) {

        (new ChannelPaymentProviderRepository(new ChannelPaymentProvider))->create([
            'channel_id' => $request->channel,
            'payment_provider_id' => $request->provider
                ]
        );

        echo json_encode(array(
            'http_code' => 200,
        ));
    }

    public function getAvailiableProducts($channelId) {
        $channel = $this->channelRepo->findChannelById(4);

        $test = (new ChannelPriceRepository(new \App\Shop\ChannelPrices\ChannelPrice))->getAvailiableProducts($channel);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($all = false) {

        $currentAuthUserId = Auth::guard('admin')->user()->id;

        if ($all === true || $currentAuthUserId === 1) {
            $list = $this->channelRepo->listChannels('id');

            if (request()->has('q') && request()->input('q') != '') {
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

        if ($request->hasFile('cover') && $request->file('cover') instanceof UploadedFile) {

            $data['cover'] = $this->channelRepo->saveCoverImage($request->file('cover'));
        }

        $validator = Validator::make($data, (new CreateChannelRequest())->rules());
        // Validate the input and return correct response
        if ($validator->fails()) {
            echo json_encode(array(
                'http_code' => 400,
                'errors' => $validator->getMessageBag()->toArray()
            ));
            die;
        }

        $channel = $this->channelRepo->createChannel($data);

        echo json_encode(array(
            'http_code' => 200,
        ));
        die;
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
    
    public function deleteProvider($id)
    {
        
        $paymentProvider = (new \App\Shop\Channels\PaymentProvider())->where('id', $id)->first();
  
        (new ChannelPaymentProviderRepository(new ChannelPaymentProvider))->deleteChannelFromProvider($paymentProvider);
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
        
        return view('admin.channels.edit', [
            'templates' => $arrTemplates,
            'products' => $arrProducts,
            'channel' => $channel,
            'channels' => $arrChannels,
            'arrProviders' => $arrPaymentProviders,
            'providers' => $arrProviders,
            'assigned_products' => $arrAssignedProducts
        ]);
    }


    /**
     * 
     * @param Request $request
     * @return type
     */
    public function update(Request $request) {


        $channel = $this->channelRepo->findChannelById($request->channel);
        $channelRepo = new ChannelRepository($channel);

        $data = $request->except('_token', '_method', 'channel');

        if ($request->hasFile('cover') && $request->file('cover') instanceof UploadedFile) {
            $data['cover'] = $channelRepo->saveCoverImage($request->file('cover'));
        }

        $validator = Validator::make($data, (new UpdateChannelRequest())->rules());
        // Validate the input and return correct response
        if ($validator->fails()) {
            echo json_encode(array(
                'http_code' => 400,
                'errors' => $validator->getMessageBag()->toArray()
            ));
            die;
        }

        $channelRepo->updateChannel($data);

        echo json_encode(array(
            'http_code' => 200,
            'message' => 'Product updated successfully'
        ));
        die;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateChannelRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function updateNewChannel(Request $request) {
        
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

    /**
     * @param Request $request
     * @param Product $channel
     */
    private function saveChannelImages(Request $request, Channel $channel) {
        if ($request->hasFile('image')) {
            $this->channelRepo->saveChannelImages(collect($request->file('image')), $channel);
        }
    }

}

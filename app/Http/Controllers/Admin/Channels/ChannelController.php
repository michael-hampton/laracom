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
        echo $request->channel;
        die('yes');
        
        $channelPriceRepo = new ChannelPriceRepository(new ChannelPrice);
        $data = $request->except('_token', '_method');
        $channelPriceRepo->createChannelPrice($data);
    }

    /**
     * 
     * @param type $channelId
     */
    public function saveChannelTemplate(Request $request) {
        
        foreach($request->templates as $template) {
            (new \App\Shop\Channels\Repositories\ChannelTemplateRepository(new \App\Shop\Channels\ChannelTemplate))->create([
            'channel_id' => $request->channel,
            'section_id' => 1,
            'title' => 'test title',
            'description' => 'test description'
                ]
        );
        }
        
        
    }

    /**
     * 
     * @param type $channelId
     */
    public function addChannelProvider(Request $request) {
        (new \App\Shop\Channels\Repositories\ChannelPaymentProviderRepository(new \App\Shop\Channels\ChannelPaymentProvider))->create([
            'channel_id' => $request->channel,
            'payment_provider_id' => $request->provider
                ]
        );
    }
    
    public function getAvailiableProducts($channelId) {
         $channel = $this->channelRepo->findChannelById(4);

        $test = (new \App\Shop\ChannelPrices\Repositories\ChannelPriceRepository(new \App\Shop\ChannelPrices\ChannelPrice))->getAvailiableProducts($channel);

        echo '<pre>';
        print_r($test);
        die;
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
    public function store(CreateChannelRequest $request) {
        $data = $request->except('_token', '_method');
        //$data['slug'] = str_slug($request->input('name'));

        if ($request->hasFile('cover') && $request->file('cover') instanceof UploadedFile) {
            $data['cover'] = $this->channelRepo->saveCoverImage($request->file('cover'));
        }

        $channel = $this->channelRepo->createChannel($data);

        $request->session()->flash('message', 'Create successful');
        return redirect()->route('admin.channels.index');
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
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {
        $channel = $this->channelRepo->findChannelById($id);

        return view('admin.channels.edit', [
            'channel' => $channel,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateChannelRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateChannelRequest $request, int $id) {
        $channel = $this->channelRepo->findChannelById($id);
        $channelRepo = new ChannelRepository($channel);

        $data = $request->except('_token', '_method');

        if ($request->hasFile('cover') && $request->file('cover') instanceof UploadedFile) {
            $data['cover'] = $channelRepo->saveCoverImage($request->file('cover'));
        }

        $channelRepo->updateChannel($data);

        $request->session()->flash('message', 'Update successful');
        return redirect()->route('admin.channels.edit', $id);
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

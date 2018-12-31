<?php

namespace App\Http\Controllers\Admin\Vouchers;

use App\Shop\Vouchers\Voucher;
use App\Shop\Vouchers\Repositories\VoucherRepository;
use App\Shop\Vouchers\VoucherGenerator;
use App\Shop\Vouchers\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Vouchers\Requests\CreateVoucherRequest;
use App\Shop\Vouchers\Requests\UpdateVoucherRequest;
use App\Shop\Vouchers\Transformations\VoucherTransformable;
use App\Http\Controllers\Controller;

class VoucherController extends Controller {

    use VoucherTransformable;

    /**
     *
     * @var VoucherRepositoryInterface $voucherRepo
     */
    private $voucherRepo;

    /**
     *
     * @var ChannelRepositoryInterface $channelRepo 
     */
    private $channelRepo;

    /**
     * 
     * @param VoucherRepositoryInterface $voucherRepository
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(
    VoucherRepositoryInterface $voucherRepository, ChannelRepositoryInterface $channelRepository
    ) {
        $this->voucherRepo = $voucherRepository;
        $this->channelRepo = $channelRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $list = $this->voucherRepo->listVoucher('expiry_date', 'desc');

        if (request()->has('q')) {
            $list = $this->voucherRepo->searchVoucher(request()->input('q'));
        }

        $vouchers = $this->mapVouchers($list);

        return view('admin.vouchers.list', ['vouchers' => $this->voucherRepo->paginateArrayResults($vouchers)]);
    }

    /**
     * 
     * @param array $list
     * @return type
     */
    private function mapVouchers($list) {
        $vouchers = $list->map(function (Voucher $voucher) {
                    return $this->transformVoucher($voucher);
                })->all();

        return $vouchers;
    }

    /**
     * 
     * @param type $channel
     */
    public function getVouchersByChannel($channel) {

        $channel = $this->channelRepo->listChannels()->where('name', $channel)->first();

        $list = $this->voucherRepo->listVoucher('expiry_date', 'desc')->where('channel', $channel->id);

        $vouchers = $this->mapVouchers($list);

        return view('admin.vouchers.list', ['vouchers' => $this->voucherRepo->paginateArrayResults($vouchers)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {

        $channels = $this->channelRepo->listChannels();

        return view('admin.vouchers.create', ['channels' => $channels]);
    }

    /**
     *  Store a newly created resource in storage.
     * 
     * @param CreateVoucherRequest $request
     * @return type
     */
    public function store(CreateVoucherRequest $request) {

        $request->request->add(['expiry_date' => date('Y-m-d', strtotime($request->expiry_date))]); //add request
        $request->request->add(['start_date' => date('Y-m-d', strtotime($request->start_date))]);

        $voucher = $this->voucherRepo->createVoucher($request->except('_token', '_method'));

        (new VoucherGenerator())->createVoucher($voucher, $request->use_count, $request->quantity);

        $request->session()->flash('message', 'Creation successful');
        return redirect()->route('admin.vouchers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id) {
        return view('admin.vouchers.show', ['voucher' => $this->voucherRepo->findVoucherById($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {

        $voucher = $this->voucherRepo->findVoucherById($id);
        $channels = $this->channelRepo->listChannels();

        return view('admin.vouchers.edit', [
            'voucher' => $voucher,
            'channels' => $channels
        ]);
    }

    /**
     * 
     * @param UpdateVoucherRequest $request
     * @param int $id
     * @return \Illuminate\Http\ResponseUpdate
     * @param UpdateVoucherRequest $request
     * @param int $id
     * @return \Illuminate\Http\ResponseUpdate the specified resource in storage.
     *
     * @param  UpdateVoucherRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVoucherRequest $request, $id) {
        $voucher = $this->voucherRepo->findVoucherById($id);

        $request->request->add(['expiry_date' => date('Y-m-d', strtotime($request->expiry_date))]); //add request
        $request->request->add(['start_date' => date('Y-m-d', strtotime($request->start_date))]);

        $update = new VoucherRepository($voucher);
        $update->updateVoucher($request->except('_method', '_token'));

        $request->session()->flash('message', 'Update successful');
        return redirect()->route('admin.vouchers.edit', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $voucher = $this->voucherRepo->findVoucherById($id);
        $delete = new VoucherRepository($voucher);
        $delete->deleteVoucher();

        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.vouchers.index');
    }

}

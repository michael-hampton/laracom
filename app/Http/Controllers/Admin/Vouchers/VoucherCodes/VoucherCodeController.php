<?php

namespace App\Http\Controllers\Admin\VoucherCodes;

use App\Shop\VoucherCodes\VoucherCode;
use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\VoucherCodes\VoucherCodeGenerator;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\VoucherCodes\Requests\CreateVoucherCodeRequest;
use App\Shop\VoucherCodes\Requests\UpdateVoucherCodeRequest;
use App\Shop\VoucherCodes\Transformations\VoucherCodeTransformable;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Channels\Channel;
use App\Http\Controllers\Controller;

class VoucherCodeController extends Controller {

    use VoucherCodeTransformable;

    private $voucherCodeRepo;

    /**
     * 
     * @param VoucherRepositoryInterface $voucherRepository
     */
    public function __construct(
    VoucherCodeRepositoryInterface $voucherCodeRepository
    ) {
        $this->voucherCodeRepo = $voucherCodeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $list = $this->voucherCodeRepo->listVoucherCode('id');

        if (request()->has('q')) {
            $list = $this->voucherCodeRepo->searchVoucherCode(request()->input('q'));
        }

        $voucherCodes = $list->map(function (VoucherCode $voucherCodes) {
                    return $this->transformVoucherCode($voucherCodes);
                })->all();

        return view('admin.voucher-codes.list', ['voucherCodes' => $this->voucherCodeRepo->paginateArrayResults($voucherCodes)]);
    }

    public function getCodesByBatch() {

        $list = $this->voucherCodeRepo->listVoucherCode('id');

        if (request()->has('q')) {
            $list = $this->voucherCodeRepo->searchVoucherCode(request()->input('q'));
        }

        $voucherCodes = $list->map(function (VoucherCode $voucherCodes) {
                    return $this->transformVoucherCode($voucherCodes);
                })->all();

        return view('admin.voucher-codes.list', ['voucherCodes' => $this->voucherCodeRepo->paginateArrayResults($voucherCodes)]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param type $id
     * @return \Illuminate\Http\Response
     */
    public function create($id) {

        return view('admin.voucher-codes.create', ['voucher_id' => $id]);
    }

    /**
     *  Store a newly created resource in storage.
     * 
     * @param CreateVoucherRequest $request
     * @return type
     */
    public function store(CreateVoucherCodeRequest $request) {

        $request->request->add(['voucher_code' => 'testcode']); //add request

        $this->voucherCodeRepo->createVoucherCode($request->except('_token', '_method'));

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
        return view('admin.voucher-codes.show', ['voucher' => $this->voucherCodeRepo->findVoucherCodeById($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {

        $voucher = $this->voucherCodeRepo->findVoucherCodeById($id);

        return view('admin.voucher-codes.edit', [
            'voucherCode' => $voucher,
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
    public function update(UpdateVoucherCodeRequest $request, $id) {
        $voucherCode = $this->voucherCodeRepo->findVoucherCodeById($id);

        $update = new VoucherCodeRepository($voucherCode);
        $update->updateVoucherCode($request->except('_method', '_token'));

        $request->session()->flash('message', 'Update successful');
        return redirect()->route('admin.voucher-codes.edit', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        $voucherCode = $this->voucherCodeRepo->findVoucherCodeById($id);
        $delete = new VoucherCodeRepository($voucherCode);
        $delete->deleteVoucherCode();

        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.voucher-codes.index');
    }

    /**
     * 
     * @param type $voucherCode
     */
    public function validateVoucherCode($voucherCode) {

        $channel = env('CHANNEL');
        $channelRepo = new ChannelRepository(new Channel);
        $channel = $channelRepo->listChannels()->where('name', $channel)->first();

        $result = $this->voucherCodeRepo->validateVoucherCode($channel, $voucherCode);

        if (!$result) {
           request()->session()->flash('message', 'Voucher could not be found');
        }
    }

}

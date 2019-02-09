<?php

namespace App\Http\Controllers\Admin\Vouchers;

use App\Shop\Vouchers\Voucher;
use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Vouchers\Repositories\VoucherRepository;
use App\Shop\Vouchers\VoucherGenerator;
use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\Vouchers\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Shop\Categories\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\VoucherCodes\VoucherCode;
use Illuminate\Http\UploadedFile;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Vouchers\Requests\CreateVoucherRequest;
use App\Shop\Vouchers\Requests\UpdateVoucherRequest;
use App\Shop\Vouchers\Transformations\VoucherTransformable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Shop\Tools\CsvTrait;
use Illuminate\Support\Facades\Validator;

class VoucherController extends Controller {

    use VoucherTransformable;
    use CsvTrait;

    /**
     *
     * @var VoucherRepositoryInterface $voucherRepo
     */
    private $voucherRepo;

    /**
     *
     * @var BrandRepositoryInterface $channelRepo 
     */
    private $brandRepo;

    /**
     *
     * @var CategoryRepositoryInterface $channelRepo 
     */
    private $categoryRepo;

    /**
     *
     * @var ProductRepositoryInterface $channelRepo 
     */
    private $productRepo;

    /**
     *
     * @var ChannelRepositoryInterface $channelRepo 
     */
    private $channelRepo;

    /**
     * 
     * @param VoucherRepositoryInterface $voucherRepository
     * @param ChannelRepositoryInterface $channelRepository
     * @param BrandRepository $brandRepository
     * @param \App\Http\Controllers\Admin\Vouchers\CategoryRepositoryInterface $categoryRepository
     * @param \App\Http\Controllers\Admin\Vouchers\ProductRepositoryInterface $productRepository
     */
    public function __construct(
    VoucherRepositoryInterface $voucherRepository, ChannelRepositoryInterface $channelRepository, BrandRepository $brandRepository, CategoryRepositoryInterface $categoryRepository, ProductRepositoryInterface $productRepository
    ) {
        $this->voucherRepo = $voucherRepository;
        $this->channelRepo = $channelRepository;
        $this->brandRepo = $brandRepository;
        $this->categoryRepo = $categoryRepository;
        $this->productRepo = $productRepository;

        $this->middleware(['permission:create-voucher, guard:admin'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:update-voucher, guard:admin'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete-voucher, guard:admin'], ['only' => ['destroy']]);
        $this->middleware(['permission:view-voucher, guard:admin'], ['only' => ['index', 'show', 'export']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $list = $this->voucherRepo->listVoucher('id', 'desc');

        if (request()->has('q'))
        {
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
     * @param type $channel
     * @return type
     */
    public function create($channel = null) {


        if (!is_null($channel))
        {
            $channels = null;
            $channel = $this->channelRepo->listChannels()->where('name', $channel)->first();
            $repo = new ChannelRepository($channel);

            $products = $repo->findProducts()->where('status', 1)->all();
        }
        else
        {
            $channels = $this->channelRepo->listChannels();
            $products = $this->productRepo->listProducts()->where('status', 1);
        }

        $scopes = !empty(env('VOUCHER_SCOPES')) ? explode(',', env('VOUCHER_SCOPES')) : [];

        return view('admin.vouchers.create', [
            'selectedChannel' => isset($channel) ? $channel->id : null,
            'channels'        => $channels,
            'scopes'          => $scopes,
            'products'        => $products,
            'brands'          => $this->brandRepo->listBrands(),
            'categories'      => $this->categoryRepo->listCategories('parent_id', 1)
                ]
        );
    }

    /**
     * 
     * @param Request $request
     * @return boolean
     */
    private function getUploadedProductIds(Request $request) {

        if ($request->has('uploadedProductCodes'))
        {

            $arrProductIds = [];
            $productCodes = explode(',', $request->uploadedProductCodes);
            $products = array_change_key_case($this->productRepo->listProducts()->where('status', 1)->keyBy('name')->toArray(), CASE_LOWER);
            $arrNotFound = [];

            if (empty($productCodes[0]))
            {

                return false;
            }

            foreach ($productCodes as $productCode)
            {

                $productCode = trim(strtolower($productCode));

                if (in_array($productCode, $products))
                {
                    $arrNotFound[] = $productCode;
                    continue;
                }

                $arrProductIds[] = $products[$productCode]['id'];
            }

            return ['product_ids' => $arrProductIds, 'not_found' => $arrNotFound];
        }

        return false;
    }

    /**
     *  Store a newly created resource in storage.
     * 
     * @param CreateVoucherRequest $request
     * @return type
     */
    public function store(Request $request) {

        $data = $request->except('_token', '_method', 'uploadedProductCodes');
        $data['expiry_date'] = date('Y-m-d', strtotime($request->expiry_date));
        $data['start_date'] = date('Y-m-d', strtotime($request->start_date));

        $arrProductIds = $this->getUploadedProductIds($request);
        $data['scope_value'] = !empty($arrProductIds) && !empty($arrProductIds['product_ids']) ? implode(',', $arrProductIds['product_ids']) : $request->scope_value;

        $validator = Validator::make($data, (new CreateVoucherRequest())->rules());

        // Validate the input and return correct response
        if ($validator->fails())
        {
            return response()->json(['http_code' => 400, 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $blImport = $request->hasFile('csv_file') && $request->file('csv_file') instanceof UploadedFile ? true : false;

        try {
            $voucher = $this->voucherRepo->createVoucher($data);

            if ($blImport === true)
            {

                $arrImportResult = $this->importVoucherCodes($request, $voucher);
            }
            else
            {
                (new VoucherGenerator())->createVoucher($voucher, $request->use_count, $request->quantity);
            }
        } catch (Exception $ex) {
            return response()->json(['http_code' => 400, 'errors' => [$ex->getMessage()]]);
        }

        $file = 'codes_' . md5(date('Y-m-d H:i:s:u')) . '.csv';
        $downloadPath = public_path('uploads/voucher_codes/' . $file);
        $this->generateCsvFile($downloadPath, $this->voucherRepo->findVoucherById($voucher->id));

        return response()->json(
                        [
                            'http_code'      => 200,
                            'import_result'  => $arrImportResult,
                            'product_result' => $arrProductIds,
                            'filename'       => asset("/uploads/voucher_codes/{$file}")
                        ]
        );
    }

    /**
     * 
     * @param Request $request
     * @param Voucher $voucher
     */
    public function importVoucherCodes(Request $request, Voucher $voucher) {

        $file_path = $request->csv_file->path();

        $arrCodes = $this->csv_to_array($file_path);
        $arrCodes = array_map("unserialize", array_unique(array_map("serialize", $arrCodes)));

        $arrExistingCodes = array_change_key_case((new VoucherCodeRepository(new VoucherCode))->listVoucherCode()->keyBy('voucher_code')->toArray(), CASE_LOWER);

        $arrDuplicates = [];
        $intAdded = 0;

        foreach ($arrCodes as $arrCode)
        {

            if (array_key_exists(strtolower($arrCode['voucher_code']), $arrExistingCodes))
            {

                $arrDuplicates[] = $arrCode['voucher_code'];
                continue;
            }

            $data = array(
                'voucher_code' => $arrCode['voucher_code'],
                'use_count'    => $request->use_count,
                'status'       => 1,
                'voucher_id'   => $voucher->id
            );

            (new VoucherCodeRepository(new VoucherCode))->createVoucherCode($data);
            $intAdded++;
        }

        return ['duplicates' => $arrDuplicates, 'added' => $intAdded];
    }

    public function updateVoucher(Request $request) {
        $data = $request->except('_token', '_method');
        $data['expiry_date'] = date('Y-m-d', strtotime($request->expiry_date));
        $data['start_date'] = date('Y-m-d', strtotime($request->start_date));

        $id = $request->id;

        $voucher = $this->voucherRepo->findVoucherById($id);

        $validator = Validator::make($data, (new UpdateVoucherRequest())->rules());

        // Validate the input and return correct response
        if ($validator->fails())
        {
            return response()->json(['http_code' => 400, 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $update = new VoucherRepository($voucher);
        $update->updateVoucher($data);

        return response()->json(['http_code' => 200]);
    }

    /**
     * 
     * @param type $pathToGenerate
     * @param Voucher $voucher
     * @return boolean
     */
    private function generateCsvFile($pathToGenerate, Voucher $voucher) {

        $arrCodes = (new VoucherCodeRepository(new VoucherCode))->listVoucherCode()->where('voucher_id', $voucher->id)->toArray();
        $this->exportToCSV($pathToGenerate, $arrCodes);

        return true;
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
        $channel = $voucher->channel;
        $objVoucherCodeRepository = new VoucherCodeRepository(new VoucherCode);

        $voucherCodes = $objVoucherCodeRepository->listVoucherCode()->where('voucher_id', $id);
        $usedVoucherCodes = $objVoucherCodeRepository->getUsedVoucherCodes($voucher);

        if (!empty($channel))
        {
            $objChannel = $this->channelRepo->findChannelById($channel);
            $repo = new ChannelRepository($objChannel);

            $products = $repo->findProducts()->where('status', 1)->all();
        }
        else
        {
            $products = $this->productRepo->listProducts()->where('status', 1);
        }

        $scopes = !empty(env('VOUCHER_SCOPES')) ? explode(',', env('VOUCHER_SCOPES')) : [];

        return view('admin.vouchers.edit', [
            'voucher'         => $voucher,
            'codes'           => $voucherCodes,
            'used'            => $usedVoucherCodes,
            'selectedChannel' => $channel,
            'scopes'          => $scopes,
            'products'        => $products,
            'brands'          => $this->brandRepo->listBrands(),
            'categories'      => $this->categoryRepo->listCategories('parent_id', 1)
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
    public function update(Request $request, $id) {
        
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

        if (!$delete->deleteVoucher())
        {
            return response()->json(['http_code' => 400, 'errors' => ['The voucher is assigned to order and cannot be deleted']]);
        }

        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.vouchers.index');
    }

}

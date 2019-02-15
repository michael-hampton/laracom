<?php

namespace App\Http\Controllers\Admin\Products;

use App\Shop\Attributes\Repositories\AttributeRepositoryInterface;
use App\Shop\AttributeValues\Repositories\AttributeValueRepositoryInterface;
use App\Shop\Brands\Repositories\BrandRepositoryInterface;
use App\Shop\Categories\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Shop\ProductAttributes\ProductAttribute;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Requests\CreateProductRequest;
use App\Shop\Products\Requests\UpdateProductRequest;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Shop\Products\Transformations\ProductTransformable;
use App\Shop\Products\Transformations\ProductCsvTransformable;
use App\Shop\Tools\UploadableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Shop\Channels\Repositories\WarehouseRepository;
use App\Shop\Channels\Warehouse;
use Illuminate\Support\Facades\Validator;
use App\Search\ProductSearch;

class ProductController extends Controller {

    use ProductTransformable,
        UploadableTrait,
        ProductCsvTransformable;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepo;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepo;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepo;

    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepo;

    /**
     * @var AttributeValueRepositoryInterface
     */
    private $attributeValueRepository;

    /**
     * @var ProductAttribute
     */
    private $productAttribute;

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepo;

    /**
     * ProductController constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeValueRepositoryInterface $attributeValueRepository
     * @param ProductAttribute $productAttribute
     * @param BrandRepositoryInterface $brandRepository
     * * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(
    ProductRepositoryInterface $productRepository, CategoryRepositoryInterface $categoryRepository, AttributeRepositoryInterface $attributeRepository, AttributeValueRepositoryInterface $attributeValueRepository, ProductAttribute $productAttribute, BrandRepositoryInterface $brandRepository, ChannelRepositoryInterface $channelRepository
    ) {
        $this->productRepo = $productRepository;
        $this->categoryRepo = $categoryRepository;
        $this->attributeRepo = $attributeRepository;
        $this->attributeValueRepository = $attributeValueRepository;
        $this->productAttribute = $productAttribute;
        $this->brandRepo = $brandRepository;
        $this->channelRepo = $channelRepository;

        $this->middleware(['permission:create-product, guard:admin'], ['only' => ['create', 'store']]);
        $this->middleware(['permission:update-product, guard:admin'], ['only' => ['edit', 'update']]);
        $this->middleware(['permission:delete-product, guard:admin'], ['only' => ['destroy']]);
        $this->middleware(['permission:view-product, guard:admin'], ['only' => ['index', 'show', 'export']]);
        $this->middleware(['permission:product-import, guard:admin'], ['only' => ['importCsv']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $categories = $this->categoryRepo->listCategories('name', 'asc')->where('parent_id', 1);
        $brands = $this->brandRepo->listBrands();

        return view('admin.products.list', [
            'categories' => $categories,
            'brands'     => $brands
        ]);
    }

    /**
     * 
     * @param Request $request
     */
    public function export(Request $request) {

        $list = ProductSearch::apply($request);

        $arrProducts = $list->map(function (Product $item) {
                    return $this->transformProductForCsv($item);
                })->all();

        return response()->json($arrProducts);
    }

    public function search(Request $request) {

        $list = ProductSearch::apply($request);

        $categories = $this->categoryRepo->listCategories('name', 'asc')->where('parent_id', 1);

        $products = $list->map(function (Product $item) {
                    return $this->transformProduct($item);
                })->all();

        return view('admin.products.search', [
            'categories' => $categories,
            'brands'     => $this->brandRepo->listBrands(),
            'products'   => $this->productRepo->paginateArrayResults($products, 10),
                ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $categories = $this->categoryRepo->listCategories('name', 'asc');
        $channels = $this->channelRepo->listChannels('name', 'asc');

        $arrWarehouses = (new WarehouseRepository(new Warehouse))->listWarehouses('name', 'asc');
        $warehouses_on = !empty(env('ALLOW_WAREHOUSES')) ? true : false;


        return view('admin.products.create', [
            'warehouses_on'  => $warehouses_on,
            'warehouses'     => $arrWarehouses,
            'categories'     => $categories,
            'channels'       => $channels,
            'brands'         => $this->brandRepo->listBrands(['*'], 'name', 'asc'),
            'default_weight' => env('SHOP_WEIGHT'),
            'weight_units'   => (new Product())->MASS_UNIT,
            'product'        => new Product
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateProductRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreateProductRequest $request) {
        $data = $request->except('_token', '_method');
        $data['slug'] = str_slug($request->input('name'));

        // cover image
        if ($request->hasFile('cover') && $request->file('cover') instanceof UploadedFile)
        {
            $data['cover'] = $this->productRepo->saveCoverImage($request->file('cover'));
        }

        $product = $this->productRepo->createProduct($data);
        
        if(!$this->productRepo->isValid()) {
                        
            $arrErrors = $this->productRepo->getValidationFailures();
  
        }
        
        $productRepo = new ProductRepository($product);

        // image
        if ($request->hasFile('image'))
        {
            $productRepo->saveProductImages(collect($request->file('image')));
        }

        // categories
        if ($request->has('categories'))
        {
            $productRepo->syncCategories($request->input('categories'));
        }
        else
        {
            $productRepo->detachCategories();
        }

        // channels
        if ($request->has('channels'))
        {

            $productRepo->syncChannels($request->input('channels'));
        }
        else
        {
            $productRepo->detachChannels();
        }

        return redirect()->route('admin.products.edit', $product->id)->with('message', 'Create successful');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id) {
        $product = $this->productRepo->findProductById($id);

        return view('admin.products.show', [
            'product' => $product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {
        $product = $this->productRepo->findProductById($id);
        $productAttributes = $product->attributes()->get();
        $qty = $productAttributes->map(function ($item) {
                    return $item->quantity;
                })->sum();
        if (request()->has('delete') && request()->has('pa'))
        {
            $pa = $productAttributes->where('id', request()->input('pa'))->first();
            $pa->attributesValues()->detach();
            $pa->delete();
            request()->session()->flash('message', 'Delete successful');
            return redirect()->route('admin.products.edit', [$product->id, 'combination' => 1]);
        }

        $categories = $this->categoryRepo->listCategories('name', 'asc')->where('parent_id', 1);
        $arrWarehouses = (new WarehouseRepository(new Warehouse))->listWarehouses('name', 'asc');
        $warehouses_on = !empty(env('ALLOW_WAREHOUSES')) ? true : false;

        return view('admin.products.edit', [
            'warehouses_on'      => $warehouses_on,
            'warehouses'         => $arrWarehouses,
            'product'            => $product,
            'images'             => $product->images()->get(['src']),
            'categories'         => $categories,
            'selectedIds'        => $product->categories()->pluck('category_id')->all(),
            'selectedChannelIds' => $product->channels()->pluck('channel_id')->all(),
            'attributes'         => $this->attributeRepo->listAttributes(),
            'productAttributes'  => $productAttributes,
            'qty'                => $qty,
            'brands'             => $this->brandRepo->listBrands(['*'], 'name', 'asc'),
            'channels'           => $this->channelRepo->listChannels('name', 'asc'),
            'weight'             => $product->weight,
            'default_weight'     => $product->mass_unit,
            'weight_units'       => (new Product())->MASS_UNIT
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateProductRequest $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     * @throws \App\Shop\Products\Exceptions\ProductUpdateErrorException
     */
    public function update(Request $request, int $id) {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {

        $product = $this->productRepo->findProductById($id);
        $product->categories()->sync([]);

        $this->productRepo->delete($id);
        return response()->json(['http_code' => 200]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeImage(Request $request) {
        $this->productRepo->deleteFile($request->only('product', 'image'), 'uploads');
        request()->session()->flash('message', 'Image delete successful');
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeThumbnail(Request $request) {
        $this->productRepo->deleteThumb($request->input('src'));
        return response()->json(['http_code' => 200]);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @return boolean
     */
    private function saveProductCombinations(Request $request, Product $product) {

        $fields = $request->only(
                'productAttributeQuantity', 'productAttributePrice', 'sale_price', 'default'
        );

        if ($errors = $this->validateFields($fields))
        {
            return redirect()->route('admin.products.edit', [$product->id, 'combination' => 1])
                            ->withErrors($errors);
        }

        $quantity = $fields['productAttributeQuantity'];
        $price = $fields['productAttributePrice'];
        $cost_price = $request->cost_price;
        $sale_price = null;

        if (isset($fields['sale_price']))
        {
            $sale_price = $fields['sale_price'];
        }
        $attributeValues = $request->input('attributeValue');
        $productRepo = new ProductRepository($product);
        $hasDefault = $productRepo->listProductAttributes()->where('default', 1)->count();
        $default = 0;
        if ($request->has('default'))
        {
            $default = $fields['default'];
        }

        if ($default == 1 && $hasDefault > 0)
        {
            $default = 0;
        }

        $productAttribute = $productRepo->saveProductAttributes(
                new ProductAttribute(compact('quantity', 'price', 'sale_price', 'cost_price', 'default'))
        );

        // save the combinations
        return collect($attributeValues)->each(function ($attributeValueId) use ($productRepo, $productAttribute) {
                    $attribute = $this->attributeValueRepository->find($attributeValueId);
                    return $productRepo->saveCombination($productAttribute, $attribute);
                })->count();
    }

    /**
     * 
     * @param Request $request
     */
    public function getProductAutoComplete(Request $request) {

        $list = $this->productRepo->searchProduct($request->product_code);

        return response()->json(['results' => $list->toArray()]);
    }

    /**
     * 
     * @param Request $request
     */
    public function saveImport(Request $request) {

        $file_path = $request->csv_file->path();

        $objProductImport = new \App\Shop\Products\ProductImport(
                $this->categoryRepo, $this->brandRepo, $this->channelRepo, $this->productRepo
        );


        if (!$objProductImport->isValid($file_path))
        {

            $arrErrors = $objProductImport->getErrors();
            return response()->json(['http_code' => '400', 'arrErrors' => $arrErrors]);
        }

        return response()->json(['http_code' => '200']);
    }

    public function updateProduct(Request $request) {

        $id = $request->id;

        $product = $this->productRepo->findProductById($id);
        $productRepo = new ProductRepository($product);

        if ($request->has('attributeValue'))
        {
            $this->saveProductCombinations($request, $product);
            return response()->json(['http_code' => 200, 'message' => 'Attribute combination created successfully']);
        }

        $data = $request->except(
                'id', 'categories', 'channels', '_token', '_method', 'default', 'image', 'productAttributeQuantity', 'productAttributePrice', 'attributeValue', 'combination'
        );

        $validator = Validator::make($data, (new UpdateProductRequest())->rules());

        // Validate the input and return correct response
        if ($validator->fails())
        {
            return response()->json(['http_code' => 400, 'errors' => $validator->getMessageBag()->toArray()]);
        }

        $data['slug'] = str_slug($request->input('name'));

        // cover image
        if ($request->hasFile('cover'))
        {
            $data['cover'] = $productRepo->saveCoverImage($request->file('cover'));
        }

        // images
        if ($request->hasFile('image'))
        {
            $productRepo->saveProductImages(collect($request->file('image')));
        }

        // categories
        if ($request->has('categories'))
        {
            $productRepo->syncCategories($request->input('categories'));
        }
        else
        {
            $productRepo->detachCategories();
        }

        // channels
        if ($request->has('channels'))
        {
            $productRepo->syncChannels($request->input('channels'));
        }
        else
        {
            $productRepo->detachChannels();
        }
        
        $productRepo->updateProduct($data);
        return response()->json(['http_code' => 200]);
    }

    public function importCsv() {
        return view('admin.products.importCsv');
    }

    /**
     * @param array $data
     *
     * @return
     */
    private function validateFields(array $data) {
        $validator = Validator::make($data, [
                    'productAttributeQuantity' => 'required'
        ]);

        if ($validator->fails(
                ))
        {
            return $validator;
        }
    }

    public function download() {
        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
            , 'Content-type'        => 'text/csv'
            , 'Content-Disposition' => 'attachment; filename=galleries.csv'
            , 'Expires'             => '0'
            , 'Pragma'              => 'public'
        ];

        $list = Product::all()->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

        $callback = function() use ($list) {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row)
            {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };


        return Response::stream($callback, 200, $headers);
    }

}

<?php

namespace App\Http\Controllers\Admin\Products;

use App\Shop\Attributes\Repositories\AttributeRepositoryInterface;
use App\Shop\AttributeValues\Repositories\AttributeValueRepositoryInterface;
use App\Shop\Brands\Repositories\BrandRepositoryInterface;
use App\Shop\Categories\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Shop\ProductAttributes\ProductAttribute;
use App\Shop\Products\Exceptions\ProductInvalidArgumentException;
use App\Shop\Products\Exceptions\ProductNotFoundException;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Requests\CreateProductRequest;
use App\Shop\Products\Requests\UpdateProductRequest;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Shop\Products\Transformations\ProductTransformable;
use App\Shop\Tools\UploadableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Search\ProductSearch;

class ProductController extends Controller {

    use ProductTransformable,
        UploadableTrait;

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

//        $this->middleware(['permission:create-product, guard:employee'], ['only' => ['create', 'store']]);
//        $this->middleware(['permission:update-product, guard:employee'], ['only' => ['edit', 'update']]);
//        $this->middleware(['permission:delete-product, guard:employee'], ['only' => ['destroy']]);
//        $this->middleware(['permission:view-product, guard:employee'], ['only' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $categories = $this->categoryRepo->listCategories('name', 'asc')->where('parent_id', 1);
        $brands = $this->brandRepo->listBrands();

        $list = $this->productRepo->listProducts('id');

        if (request()->has('q') && request()->input('q') != '') {
            $list = $this->productRepo->searchProduct(request()->input('q'));
        }

        $products = $list->map(function (Product $item) {
                    return $this->transformProduct($item);
                })->all();

        return view('admin.products.list', [
            'products' => $this->productRepo->paginateArrayResults($products, 10),
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    public function search(Request $request) {

        $list = ProductSearch::apply($request);

        $categories = $this->categoryRepo->listCategories('name', 'asc')->where('parent_id', 1);

        $products = $list->map(function (Product $item) {
                    return $this->transformProduct($item);
                })->all();

        return view('admin.products.list', [
            'categories' => $categories,
            'brands' => $this->brandRepo->listBrands(),
            'products' => $this->productRepo->paginateArrayResults($products, 10),
                ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $categories = $this->categoryRepo->listCategories('name', 'asc')->where('parent_id', 1);
        $channels = $this->channelRepo->listChannels('name', 'asc');

        return view('admin.products.create', [
            'categories' => $categories,
            'channels' => $channels,
            'brands' => $this->brandRepo->listBrands(['*'], 'name', 'asc'),
            'default_weight' => env('SHOP_WEIGHT'),
            'weight_units' => (new Product())->MASS_UNIT,
            'product' => new Product
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
        if ($request->hasFile('cover') && $request->file('cover') instanceof UploadedFile) {
            $data['cover'] = $this->productRepo->saveCoverImage($request->file('cover'));
        }

        $product = $this->productRepo->createProduct($data);
        $productRepo = new ProductRepository($product);

        // image
        if ($request->hasFile('image')) {
            $productRepo->saveProductImages(collect($request->file('image')));
        }

        // categories
        if ($request->has('categories')) {
            $productRepo->syncCategories($request->input('categories'));
        } else {
            $productRepo->detachCategories();
        }

        // channels
        if ($request->has('channels')) {

            $productRepo->syncChannels($request->input('channels'));
        } else {
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
        if (request()->has('delete') && request()->has('pa')) {
            $pa = $productAttributes->where('id', request()->input('pa'))->first();
            $pa->attributesValues()->detach();
            $pa->delete();
            request()->session()->flash('message', 'Delete successful');
            return redirect()->route('admin.products.edit', [$product->id, 'combination' => 1]);
        }

        $categories = $this->categoryRepo->listCategories('name', 'asc')->where('parent_id', 1);

        return view('admin.products.edit', [
            'product' => $product,
            'images' => $product->images()->get(['src']),
            'categories' => $categories,
            'selectedIds' => $product->categories()->pluck('category_id')->all(),
            'selectedChannelIds' => $product->channels()->pluck('channel_id')->all(),
            'attributes' => $this->attributeRepo->listAttributes(),
            'productAttributes' => $productAttributes,
            'qty' => $qty,
            'brands' => $this->brandRepo->listBrands(['*'], 'name', 'asc'),
            'channels' => $this->channelRepo->listChannels('name', 'asc'),
            'weight' => $product->weight,
            'default_weight' => $product->mass_unit,
            'weight_units' => (new Product())->MASS_UNIT
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
    public function update(UpdateProductRequest $request, int $id) {
        $product = $this->productRepo->findProductById($id);
        $productRepo = new ProductRepository($product);

        if ($request->has('attributeValue')) {
            $this->saveProductCombinations($request, $product);
            return redirect()->route('admin.products.edit', [$id, 'combination' => 1])
                            ->with('message', 'Attribute combination created successful');
        }

        $data = $request->except(
                'categories', 'channels', '_token', '_method', 'default', 'image', 'productAttributeQuantity', 'productAttributePrice', 'attributeValue', 'combination'
        );

        $data['slug'] = str_slug($request->input('name'));

        // cover image
        if ($request->hasFile('cover')) {
            $data['cover'] = $productRepo->saveCoverImage($request->file('cover'));
        }

        // images
        if ($request->hasFile('image')) {
            $productRepo->saveProductImages(collect($request->file('image')));
        }

        // categories
        if ($request->has('categories')) {
            $productRepo->syncCategories($request->input('categories'));
        } else {
            $productRepo->detachCategories();
        }

        // channels
        if ($request->has('channels')) {
            $productRepo->syncChannels($request->input('channels'));
        } else {
            $productRepo->detachChannels();
        }

        $productRepo->updateProduct($data);

        return redirect()->route('admin.products.edit', $id)
                        ->with('message', 'Update successful');
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

        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.products.index');
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
        request()->session()->flash('message', 'Image delete successful');
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param Product $product
     */
    private function saveProductImages(Request $request, Product $product) {
        if ($request->hasFile('image')) {
            $this->productRepo->saveProductImages(collect($request->file('image')), $product);
        }
    }

    /**
     * @param Request $request
     * @param Product $product
     * @return boolean
     */
    private function saveProductCombinations(Request $request, Product $product): bool {
        $fields = $request->only(
                'productAttributeQuantity', 'productAttributePrice', 'sale_price', 'default'
        );

        if ($errors = $this->validateFields($fields)) {
            return redirect()->route('admin.products.edit', [$product->id, 'combination' => 1])
                            ->withErrors($errors);
        }

        $quantity = $fields['productAttributeQuantity'];
        $price = $fields['productAttributePrice'];
        $sale_price = null;

        if (isset($fields['sale_price'])) {
            $sale_price = $fields['sale_price'];
        }

        $attributeValues = $request->input('attributeValue');
        $productRepo = new ProductRepository($product);

        $hasDefault = $productRepo->listProductAttributes()->where('default', 1)->count();

        $default = 0;

        if ($request->has('default')) {
            $default = $fields['default'];
        }

        if ($default == 1 && $hasDefault > 0) {
            $default = 0;
        }

        $productAttribute = $productRepo->saveProductAttributes(
                new ProductAttribute(compact('quantity', 'price', 'sale_price', 'default'))
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

        echo json_encode(['results' => $list->toArray()]);
    }
    
    public function saveImport(Request $request) {
        $file_path = $request->csv_file->path();
        $line = 0;
        $arrProducts = [];
        
        if (($handle = fopen($file_path, "r")) !== FALSE) {
            $flag = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                
                if ($flag) {
                    $flag = false;
                    continue;
                }
                
                list(
                        $order['name'],
                        $order['channels'],
                        $order['categories'],
                        $order['brand'],
                        $order['sku'],
                        $order['description'],
                        $order['quantity'],
                        $order['price'],
                        $order['sale_price'],
                        $order['weight'],
                        $order['mass_unit'],
                        $order['length'],
                        $order['width'],
                        $order['height'],
                        $order['distance_unit'],
                        ) = $data;
                $line++;
               
                $csv_errors = Validator::make(
                                $order, (new ImportRequest())->rules()
                        )->errors();
               
                $categories = explode(',', $order['categories']);
                
                $arrCategories = [];
                
                foreach($categories as $category) {
                    $categoryId = $this->categoryRepo->findByName($category);
                    
                    if (empty($categoryId)) {
                        $csv_errors->add('category', "Category is invalid.");
                    } else {
                        $arrCategories[] = $categoryId;
                    }
                }
                
                $brand = $this->brandRepo->findByName($order['brand']);
                
                if (empty($brand)) {
                    $csv_errors->add('brand', "Brand is invalid.");
                }
               
                $channels = explode(',', $order['channels']);
                
                $arrChannels = [];
                
                foreach($channels as $channel) {
                    $channelId = $this->channelRepo->findByName($channel);
                    
                    if (empty($channelId)) {
                        $csv_errors->add('channel', "Channel is invalid.");
                    } else {
                        $arrChannels[] = $channelId;
                    }
                }
                
                if ($csv_errors->any()) {
                    return redirect()->back()
                                    ->withErrors($csv_errors, 'import')
                                    ->with('error_line', $line);
                }
                
                $arrProducts[] = [
                    'name' => $order['name'],
                    'sku' => $order['sku'],
                    'description' => $order['description'],
                    'quantity' => $order['quantity'],
                    'price' => $order['price'],
                    'status' => 1,
                    'weight' => $order['weight'],
                    'mass_unit' => $order['mass_unit'],
                    'sale_price' => $order['sale_price'],
                    'length' => $order['length'],
                    'width' => $order['width'],
                    'height' => $order['height'],
                    'distance_unit' => $order['distance_unit'],
                    'categories' => $arrCategories,
                    'channels' => $arrChannels,
                    'brand' => $brand->id
                ];
               
                
            }
            fclose($handle);
        }
        
        foreach ($arrProducts as $arrProduct) {
            
            $arrCategories = $arrProduct['categories'];
            $arrChannels = $arrProduct['channels'];
            
            unset($arrProduct['categories']);
            unset($arrProduct['channels']);
            
            $data['slug'] = str_slug($arrProduct['name']);

            $product = $this->productRepo->createProduct($arrProduct);
            $productRepo = new ProductRepository($product);

            // categories
            if (!empty($arrCategories)) {
                $productRepo->syncCategories($arrCategories);
            }

            // channels
            if (!empty($arrChannels)) {

                $productRepo->syncChannels($arrChannels);
            } 
        }
        
        request()->session()->flash('message', 'Import successful');
        return redirect()->route('admin.products.importCsv');
    }
    
    public function importCsv() {
        return view('admin.orders.importCsv');
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

        if ($validator->fails()) {
            return $validator;
        }
    }

}

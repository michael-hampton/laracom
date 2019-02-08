<?php

namespace App\Shop\Products\Repositories;

use App\Shop\AttributeValues\AttributeValue;
use App\Shop\Products\Exceptions\ProductCreateErrorException;
use App\Shop\Products\Exceptions\ProductUpdateErrorException;
use App\Shop\Tools\UploadableTrait;
use App\Shop\Base\BaseRepository;
use App\Shop\Brands\Brand;
use App\Shop\Channels\Channel;
use App\Shop\ProductAttributes\ProductAttribute;
use App\Shop\ProductImages\ProductImage;
use App\Shop\Products\Exceptions\ProductNotFoundException;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\Interfaces\ProductRepositoryInterface;
use App\Shop\Products\Transformations\ProductTransformable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface {

    use ProductTransformable,
        UploadableTrait;

    /**
     * ProductRepository constructor.
     * @param Product $product
     */
    public function __construct(Product $product) {
        parent::__construct($product);
        $this->model = $product;
    }

    /**
     * List all the products
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return Collection
     */
    public function listProducts(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }

    /**
     * Create the product
     *
     * @param array $data
     *
     * @return Product
     * @throws ProductCreateErrorException
     */
    public function createProduct(array $data): Product {
        try {
            return $this->create($data);
        } catch (QueryException $e) {
            throw new ProductCreateErrorException($e);
        }
    }

    /**
     * Update the product
     *
     * @param array $data
     *
     * @return bool
     * @throws ProductUpdateErrorException
     */
    public function updateProduct(array $data): bool {
        $filtered = collect($data)->except('image')->all();
        try {
            return $this->model->where('id', $this->model->id)->update($filtered);
        } catch (QueryException $e) {
            throw new ProductUpdateErrorException($e);
        }
    }

    /**
     * Find the product by ID
     *
     * @param int $id
     *
     * @return Product
     * @throws ProductNotFoundException
     */
    public function findProductById(int $id): Product {
        try {
            return $this->transformProduct($this->findOneOrFail($id));
        } catch (ModelNotFoundException $e) {
            throw new ProductNotFoundException($e);
        }
    }

    /**
     * Delete the product
     *
     * @param Product $product
     *
     * @return bool
     * @throws \Exception
     * @deprecated
     * @use removeProduct
     */
    public function deleteProduct(Product $product): bool {
        $product->images()->delete();
        return $product->delete();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function removeProduct(): bool {
        return $this->model->where('id', $this->model->id)->delete();
    }

    /**
     * Detach the categories
     */
    public function detachCategories() {
        $this->model->categories()->detach();
    }

    /**
     * Return the categories which the product is associated with
     *
     * @return Collection
     */
    public function getCategories(): Collection {
        return $this->model->categories()->get();
    }

    /**
     * Sync the categories
     *
     * @param array $params
     */
    public function syncCategories(array $params) {
        $this->model->categories()->sync($params);
    }

    /**
     * Detach the categories
     */
    public function detachChannels() {
        $this->model->channels()->detach();
    }

    /**
     * Return the categories which the product is associated with
     *
     * @return Collection
     */
    public function getChannels(): Collection {
        return $this->model->channels()->get();
    }

    /**
     * Sync the categories
     *
     * @param array $params
     */
    public function syncChannels(array $params) {
        $this->model->channels()->sync($params);
    }

    /**
     * @param $file
     * @param null $disk
     * @return bool
     */
    public function deleteFile(array $file, $disk = null): bool {
        return $this->update(['cover' => null], $file['product']);
    }

    /**
     * @param string $src
     * @return bool
     */
    public function deleteThumb(string $src): bool {
        return DB::table('product_images')->where('src', $src)->delete();
    }

    /**
     * Get the product via slug
     *
     * @param array $slug
     *
     * @return Product
     * @throws ProductNotFoundException
     */
    public function findProductBySlug(array $slug): Product {
        try {
            return $this->findOneByOrFail($slug);
        } catch (ModelNotFoundException $e) {
            throw new ProductNotFoundException($e);
        }
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function searchProduct(string $text): Collection {
        if (!empty($text))
        {
            return $this->model->searchProduct($text);
        }
        else
        {
            return $this->listProducts();
        }
    }

    /**
     * @return mixed
     */
    public function findProductImages(): Collection {
        return $this->model->images()->get();
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function saveCoverImage(UploadedFile $file): string {
        return $file->store('products', ['disk' => 'images']);
    }

    /**
     * @param Collection $collection
     *
     * @return void
     */
    public function saveProductImages(Collection $collection) {
        $collection->each(function (UploadedFile $file) {
            $filename = $this->storeFile($file);
            $productImage = new ProductImage([
                'product_id' => $this->model->id,
                'src'        => $filename
            ]);
            $this->model->images()->save($productImage);
        });
    }

    /**
     * Associate the product attribute to the product
     *
     * @param ProductAttribute $productAttribute
     * @return ProductAttribute
     */
    public function saveProductAttributes(ProductAttribute $productAttribute): ProductAttribute {
        $this->model->attributes()->save($productAttribute);
        return $productAttribute;
    }

    /**
     * List all the product attributes associated with the product
     *
     * @return Collection
     */
    public function listProductAttributes(): Collection {
        return $this->model->attributes()->get();
    }

    /**
     * Delete the attribute from the product
     *
     * @param ProductAttribute $productAttribute
     *
     * @return bool|null
     * @throws \Exception
     */
    public function removeProductAttribute(ProductAttribute $productAttribute): bool {
        return $productAttribute->delete();
    }

    /**
     * @param ProductAttribute $productAttribute
     * @param AttributeValue ...$attributeValues
     *
     * @return Collection
     */
    public function saveCombination(ProductAttribute $productAttribute, AttributeValue ...$attributeValues): Collection {
        return collect($attributeValues)->each(function (AttributeValue $value) use ($productAttribute) {
                    return $productAttribute->attributesValues()->save($value);
                });
    }

    /**
     * @return Collection
     */
    public function listCombinations(): Collection {
        return $this->model->attributes()->map(function (ProductAttribute $productAttribute) {
                    return $productAttribute->attributesValues;
                });
    }

    /**
     * @param ProductAttribute $productAttribute
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findProductCombination(ProductAttribute $productAttribute) {
        $values = $productAttribute->attributesValues()->get();
        return $values->map(function (AttributeValue $attributeValue) {
                    return $attributeValue;
                })->keyBy(function (AttributeValue $item) {
                    return strtolower($item->attribute->name);
                })->transform(function (AttributeValue $value) {
                    return $value->value;
                });
    }

    /**
     * @param Brand $brand
     */
    public function saveBrand(Brand $brand) {
        $this->model->brand()->associate($brand);
    }

    /**
     * @return Brand
     */
    public function findBrand() {
        return $this->model->brand;
    }

    /**
     * 
     * @param type $name
     * @return type
     */
    public function findByName(string $name) {
        $query = DB::table('products');
        $query->whereRaw('LOWER(`name`) = ? ', [trim(strtolower($name))]);
        $result = $query->get();
        return Product::hydrate($result->toArray())[0];
    }

    /**
     * 
     * @param Request $request
     * @param Channel $channel
     */
    public function filterProducts(Request $request, Channel $channel = null) {

        $query = $this->model->where('status', 1);

        $arrOrderBy = explode(' ', $request->order_by);
        $orderByField = $arrOrderBy[0];
        $orderDirection = $arrOrderBy[1];

        if (!empty($channel))
        {
            $query->join('channel_product', 'products.id', '=', 'channel_product.product_id');
            $query->where('channel_product.channel_id', $channel->id);
        }
        
          if ($request->has('category'))
        {
            $query->join('category_product', 'products.id', '=', 'category_product.product_id');
            $query->where('category_product.category_id', $request->category);
        }

        if ($request->has('minimum_price') && $request->has('maximum_price'))
        {
            $query->whereBetween('channel_product.price', [$request->minimum_price, $request->maximum_price]);
        }

        if ($request->has('in_stock') && $request->in_stock === 'true')
        {      
            $query->whereRaw('products.quantity - products.reserved_stock > 0');
        }

        if ($request->has('brand') && !empty($request->brand))
        {
            $query->whereIn('products.brand_id', $request->brand);
        }

        $query->groupBy('products.id');

        $query->orderBy($orderByField, strtolower($orderDirection));

        $result = $query->paginate(20);

        return $result;
    }

}

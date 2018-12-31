<?php
namespace App\Shop\Products\Repositories\Interfaces;
use App\Shop\AttributeValues\AttributeValue;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Brands\Brand;
use App\Shop\ProductAttributes\ProductAttribute;
use App\Shop\Products\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/**
 * 
 */
interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     */
    public function listProducts(string $order = 'id', string $sort = 'desc', array $columns = ['*']) : Collection;
    
    /**
     * 
     * @param array $data
     */
    public function createProduct(array $data) : Product;
    
    /**
     * 
     * @param array $data
     */
    public function updateProduct(array $data) : bool;
    
    /**
     * 
     * @param int $id
     */
    public function findProductById(int $id) : Product;
    
    /**
     * 
     * @param Product $product
     */
    public function deleteProduct(Product $product) : bool;
    
    /**
     * 
     */
    public function removeProduct() : bool;
    
    /**
     * 
     */
    public function detachCategories();
    
    /**
     * 
     */
    public function getCategories() : Collection;
    
    /**
     * 
     * @param array $params
     */
    public function syncCategories(array $params);
    
     /**
     * 
     */
    public function detachChannels();
    
    /**
     * 
     */
    public function getChannels() : Collection;
    
    /**
     * 
     * @param array $params
     */
    public function syncChannels(array $params);
    
    /**
     * 
     * @param array $file
     * @param type $disk
     */
    public function deleteFile(array $file, $disk = null) : bool;
    
    /**
     * 
     * @param string $src
     */
    public function deleteThumb(string $src) : bool;
    
    /**
     * 
     * @param array $slug
     */
    public function findProductBySlug(array $slug) : Product;
    
    /**
     * 
     * @param string $text
     */
    public function searchProduct(string $text) : Collection;
    
    /**
     * 
     */
    public function findProductImages() : Collection;
    
    /**
     * 
     * @param UploadedFile $file
     */
    public function saveCoverImage(UploadedFile $file) : string;
    
    /**
     * 
     * @param Collection $collection
     */
    public function saveProductImages(Collection $collection);
    
    /**
     * 
     * @param ProductAttribute $productAttribute
     */
    public function saveProductAttributes(ProductAttribute $productAttribute) : ProductAttribute;
    
    /**
     * 
     */
    public function listProductAttributes() : Collection;
    
    /**
     * 
     * @param ProductAttribute $productAttribute
     */
    public function removeProductAttribute(ProductAttribute $productAttribute) : bool;
    
    /**
     * 
     * @param ProductAttribute $productAttribute
     * @param AttributeValue $attributeValues
     */
    public function saveCombination(ProductAttribute $productAttribute, AttributeValue ...$attributeValues) : Collection;
    
    /**
     * 
     */
    public function listCombinations() : Collection;
    
    /**
     * 
     * @param ProductAttribute $attribute
     */
    public function findProductCombination(ProductAttribute $attribute);
    
    /**
     * 
     * @param Brand $brand
     */
    public function saveBrand(Brand $brand);
    
    /**
     * 
     */
    public function findBrand();
}
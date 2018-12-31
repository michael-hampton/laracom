<?php

namespace App\Shop\Brands\Repositories;

use App\Shop\Brands\Brand;
use App\Shop\Products\Product;
use Illuminate\Support\Collection;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;

interface BrandRepositoryInterface extends BaseRepositoryInterface
{
    public function createBrand(array $data): Brand;

    public function findBrandById(int $id) : Brand;

    public function updateBrand(array $data) : bool;

    public function deleteBrand() : bool;

    public function listBrands($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc') : Collection;

    public function saveProduct(Product $product);
}

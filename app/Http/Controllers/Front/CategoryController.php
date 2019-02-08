<?php

namespace App\Http\Controllers\Front;

use App\Shop\Categories\Repositories\CategoryRepository;
use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Brands\Brand;
use App\Shop\Categories\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Http\Controllers\Controller;

class CategoryController extends Controller {

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepo;

    /**
     * CategoryController constructor.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository) {
        $this->categoryRepo = $categoryRepository;
    }

    /**
     * Find the category via the slug
     *
     * @param string $slug
     * @return \App\Shop\Categories\Category
     */
    public function getCategory(string $slug) {
        $category = $this->categoryRepo->findCategoryBySlug(['slug' => $slug]);

        $repo = new CategoryRepository($category);

        $productObjects = $repo->findProducts()->where('status', 1);
        $brand_ids = array_unique($productObjects->pluck('brand_id')->toArray());
        
        $cost = $productObjects->pluck('price')->toArray();
        $products = $productObjects->all();
        $brands = (new BrandRepository(new Brand))->getBrandsForGivenIds($brand_ids);
        
        $cost[] = 100;
        $cost[] = 1;
       

        return view('front.categories.category', [
            'brands' => $brands,
            'cost' => $cost,
            'category' => $category
        ]);
    }

}

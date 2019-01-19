<?php
namespace App\Http\Controllers\Front;
use App\Shop\Categories\Repositories\CategoryRepository;
use App\Shop\Categories\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Http\Controllers\Controller;
class CustomerReturnController extends Controller
{
    /**
     * @var ReturnRepositoryInterface
     */
    private $returnRepo;
    
    /**
     * @var ReturnLineRepositoryInterface
     */
    private $returnLineRepo;
}

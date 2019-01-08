<?php
namespace App\Shop\CourierRates\Repositories\Interfaces;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\CourierRates\CourierRate;
use Illuminate\Support\Collection;
interface CourierRateRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * 
     * @param array $data
     */
    public function createCourierRate(array $data) : CourierRate;
    
    /**
     * 
     * @param array $params
     */
    public function updateCourierRate(array $params) : CourierRate;
    
    /**
     * 
     * @param int $id
     */
    public function findCourierRateById(int $id) : CourierRate;
    
    /**
     * 
     * @param string $order
     * @param string $sort
     */
     //public function listCourierRates(string $order = 'id', string $sort = 'desc') : Collection;
}

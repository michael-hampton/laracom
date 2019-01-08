<?php
namespace App\Shop\CourierRates\Repositories\Interfaces;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\CourierRates\CourierRate;
use Illuminate\Support\Collection;
interface CourierRateRepositoryInterface extends BaseRepositoryInterface
{
    public function createCourierRate(array $data) : CourierRate;
    public function updateCourierRate(array $params) : CourierRate;
    public function findCourierRateById(int $id) : CourierRate;
    public function listCourierRates(string $order = 'id', string $sort = 'desc') : Collection;
}

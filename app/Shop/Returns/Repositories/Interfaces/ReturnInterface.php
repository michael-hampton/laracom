<?php
namespace App\Shop\Returns\Repositories\Interfaces;
use App\Shop\Returns\Return;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Orders\Order;
use Illuminate\Support\Collection;
interface ReturnRepositoryInterface extends BaseRepositoryInterface {
    /**
     * 
     * @param array $params
     */
    public function createReturn(array $params): Return;
    /**
     * 
     * @param array $update
     */
    public function updateReturn(array $update): bool;
    /**
     * 
     */
    public function deleteReturn();
    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     */
    public function listReturn(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection;
    /**
     * 
     * @param int $id
     */
    public function findReturnById(int $id): Return;
    /**
     * 
     * @param string $text
     */
    public function searchReturn(string $text): Collection;
}

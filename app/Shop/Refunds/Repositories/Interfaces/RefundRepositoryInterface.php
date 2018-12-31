<?php

namespace App\Shop\Refunds\Repositories\Interfaces;

use App\Shop\Refunds\Refund;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Orders\Order;
use Illuminate\Support\Collection;

interface RefundRepositoryInterface extends BaseRepositoryInterface {

    /**
     * 
     * @param array $params
     */
    public function createRefund(array $params): Refund;

    /**
     * 
     * @param array $update
     */
    public function updateRefund(array $update): bool;

    /**
     * 
     */
    public function deleteRefund();

    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     */
    public function listRefund(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection;

    /**
     * 
     * @param int $id
     */
    public function findRefundById(int $id): Refund;

    /**
     * 
     * @param string $text
     */
    public function searchRefund(string $text): Collection;
}

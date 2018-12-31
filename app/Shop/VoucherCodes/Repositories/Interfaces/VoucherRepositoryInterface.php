<?php

namespace App\Shop\VoucherCodes\Repositories\Interfaces;

use App\Shop\VoucherCodes\VoucherCode;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface VoucherCodeRepositoryInterface extends BaseRepositoryInterface {

    /**
     * 
     * @param array $params
     */
    public function createVoucherCode(array $params): VoucherCode;

    /**
     * 
     * @param array $update
     */
    public function updateVoucherCode(array $update): bool;

    /**
     * 
     */
    public function deleteVoucherCode();

    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     */
    public function listVoucherCode(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection;

    /**
     * 
     * @param int $id
     */
    public function findVoucherCodeById(int $id): VoucherCode;

    /**
     * 
     * @param string $text
     */
    public function searchVoucherCode(string $text): Collection;
}

<?php

namespace App\Shop\Vouchers\Repositories\Interfaces;

use App\Shop\Vouchers\Voucher;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Orders\Order;
use Illuminate\Support\Collection;

interface VoucherRepositoryInterface extends BaseRepositoryInterface {

    /**
     * 
     * @param array $params
     */
    public function createVoucher(array $params): Voucher;

    /**
     * 
     * @param array $update
     */
    public function updateVoucher(array $update): bool;

    /**
     * 
     */
    public function deleteVoucher();

    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     */
    public function listVoucher(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection;

    /**
     * 
     * @param int $id
     */
    public function findVoucherById(int $id): Voucher;

    /**
     * 
     * @param string $text
     */
    public function searchVoucher(string $text): Collection;
}

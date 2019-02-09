<?php

namespace App\Shop\Vouchers\Repositories;

use App\Shop\Vouchers\Voucher;
use App\Shop\Vouchers\Exceptions\VoucherInvalidArgumentException;
use App\Shop\Vouchers\Exceptions\VoucherNotFoundException;
use App\Shop\Vouchers\Repositories\Interfaces\VoucherRepositoryInterface;
use App\Shop\Vouchers\Transformations\VoucherTransformable;
use App\Shop\Base\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use App\Shop\Orders\Order;
use Illuminate\Support\Facades\DB;
use App\Traits\VoucherValidationScope;

class VoucherRepository extends BaseRepository implements VoucherRepositoryInterface {

    use VoucherTransformable,
        VoucherValidationScope;

    /**
     * VoucherRepository constructor.
     * @param Voucher $voucher
     */
    public function __construct(Voucher $voucher) {
        parent::__construct($voucher);
        $this->model = $voucher;
    }

    /**
     * Create the voucher
     *
     * @param array $params
     * @return Address
     */
    public function createVoucher(array $params): Voucher {
        try {

            $voucher = new Voucher($params);

            $voucher->save();

            return $voucher;
        } catch (QueryException $e) {
            throw new VoucherInvalidArgumentException('Voucher creation error', 500, $e);
        }
    }

    /**
     * @param array $update
     * @return bool
     */
    public function updateVoucher(array $update): bool {
        return $this->model->update($update);
    }

    /**
     * Soft delete the voucher
     *
     */
    public function deleteVoucher() {

        if ($this->checkIfVoucherUsed())
        {

            return false;
        }

        return $this->model->delete();
    }

    /**
     * 
     * @return boolean
     */
    private function checkIfVoucherUsed() {

        $result = $this->model
                ->join('voucher_codes', 'vouchers.id', '=', 'voucher_codes.voucher_id')
                ->join('orders', 'orders.voucher_code', '=', 'voucher_codes.id')
                ->where('vouchers.id', $this->model->id)
                ->get();

        return !empty($result);
    }

    /**
     * List all the voucher
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return array|Collection
     */
    public function listVoucher(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }

    /**
     * Return the voucher
     *
     * @param int $id
     * @return Address
     */
    public function findVoucherById(int $id): Voucher {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new VoucherNotFoundException($e->getMessage());
        }
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function searchVoucher(string $text): Collection {
        return $this->model->search($text, [
                    'coupon_code' => 10,
                    'amount'      => 5,
                    'amount_type' => 10
                ])->get();
    }
    
    /**
     * 
     * @param int $id
     * @param type $cartProducts
     * @return boolean
     */
    public function validateVoucher(int $id, $cartProducts) {

        $objVoucher = $this->findVoucherById($id);

        if (!$this->validateVoucherScopes($objVoucher, $cartProducts))
        {
            $this->validationFailures[] = 'unable to validate voucher code';
            return false;
        }

        return $objVoucher;
    }

}

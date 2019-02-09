<?php

namespace App\Shop\VoucherCodes\Repositories;

use App\Shop\VoucherCodes\VoucherCode;
use App\Shop\Vouchers\Voucher;
use App\Shop\VoucherCodes\Exceptions\VoucherCodeInvalidArgumentException;
use App\Shop\VoucherCodes\Exceptions\VoucherCodeNotFoundException;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\VoucherCodes\Transformations\VoucherCodeTransformable;
use App\Shop\Base\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use App\Shop\Channels\Channel;
use App\Shop\Vouchers\Repositories\VoucherRepository;
use Illuminate\Support\Facades\DB;

/**
 * 
 */
class VoucherCodeRepository extends BaseRepository implements VoucherCodeRepositoryInterface {

    use VoucherCodeTransformable;

    /**
     *
     * @var type 
     */
    private $validationFailures = [];

    /**
     * VoucherRepository constructor.
     * @param Voucher $voucher
     */
    public function __construct(VoucherCode $voucherCode) {
        parent::__construct($voucherCode);
        $this->model = $voucherCode;
    }

    /**
     * 
     * @return type
     */
    public function getValidationFailures() {
        return $this->validationFailures;
    }

    /**
     * Create the voucher
     *
     * @param array $params
     * @return Address
     */
    public function createVoucherCode(array $params): VoucherCode {
        try {

            $voucherCode = new VoucherCode($params);

            $voucherCode->save();

            return $voucherCode;
        } catch (QueryException $e) {
            throw new VoucherCodeInvalidArgumentException('Voucher Code creation error', 500, $e);
        }
    }

    /**
     * @param array $update
     * @return bool
     */
    public function updateVoucherCode(array $update): bool {
        return $this->model->update($update);
    }

    /**
     * Soft delete the voucher code
     *
     */
    public function deleteVoucherCode() {

        if ($this->checkVoucherCodeIsUsed())
        {
            return false;
        }

        return $this->model->delete();
    }

    public function checkVoucherCodeIsUsed() {
        $check = DB::table('orders')->where('voucher_code', $this->model->id)->first();

        if (!empty($check))
        {
            return true;
        }

        return false;
    }

    /**
     * List all the voucher codes
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return array|Collection
     */
    public function listVoucherCode(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }

    /**
     * Return the voucher
     *
     * @param int $id
     * @return Address
     */
    public function findVoucherCodeById(int $id): VoucherCode {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new VoucherCodeNotFoundException($e->getMessage());
        }
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function searchVoucherCode(string $text): Collection {
        return $this->model->search($text, [
                    'use_count'    => 10,
                    'voucher_code' => 10,
                    'voucher_id'   => 10
                ])->get();
    }

    /**
     * Get the voucher codes via batch number
     *
     * @param array $batch_number
     * @return Product
     */
    public function findCodesByBatchNumber(array $batch_number): Product {
        try {
            return $this->findBy($batch_number);
        } catch (ModelNotFoundException $e) {
            throw new VoucherCodeNotFoundException($e->getMessage());
        }
    }

    /**
     * 
     * @param Channel $channel
     * @param string $voucherCode
     * @param type $cartProducts
     * @param VoucherRepository $voucherRepo
     * @return boolean
     */
    public function validateVoucherCode(Channel $channel, string $voucherCode, $cartProducts, VoucherRepository $voucherRepo) {

        $results = DB::select(DB::raw("SELECT *, 
                                            vc.id AS code_id 
                                     FROM   voucher_codes vc 
                                            INNER JOIN vouchers v 
                                                    ON v.id = vc.voucher_id 
                                     WHERE  voucher_code = :code 
                                            AND ( use_count > 0 ) 
                                            AND ( expiry_date IS NULL 
                                                   OR expiry_date > Date(Now()) ) 
                                            AND Date(Now()) >= start_date 
                                            AND v.status = 1 
                                            AND channel = :channel"), [
                    'channel' => $channel->id,
                    'code'    => $voucherCode
                        ]
        );

        if (empty($results))
        {
            $this->validationFailures[] = 'unable to find voucher code';
            return false;
        }

        try {
            $objVoucherCode = $this->findVoucherCodeById($results[0]->code_id);

            if (!$voucherRepo->validateVoucher($objVoucherCode->voucher_id, $cartProducts))
            {
                $this->validationFailures[] = 'unable to validate voucher';
                return false;
            }
        } catch (\Exception $e) {
            $this->validationFailures[] = $e->getMessage();
        }

        request()->session()->put('voucherCode', $objVoucherCode->voucher_code);

        return $objVoucherCode;
    }

    /**
     * 
     * @param Channel $channel
     * @return type
     */
    public function getUsedVoucherCodes(Voucher $voucher) {

        $result = $this->model
                ->select('voucher_codes.*')
                ->join('orders', 'orders.voucher_code', '=', 'voucher_codes.id')
                ->where('voucher_codes.voucher_id', $voucher->id)
                ->groupBy('voucher_codes.id')
                ->get();

        return $result;
    }

    /**
     * 
     * @param type $voucherCode
     * @return type
     * @throws VoucherCodeNotFoundException
     */
    public function getByVoucherCode($voucherCode) {

        try {
            return $this->model->where('voucher_code', $voucherCode)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new VoucherCodeNotFoundException($e->getMessage());
        }
    }

}

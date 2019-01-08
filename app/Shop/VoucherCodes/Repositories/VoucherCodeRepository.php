<?php

namespace App\Shop\VoucherCodes\Repositories;

use App\Shop\VoucherCodes\VoucherCode;
use App\Shop\VoucherCodes\Exceptions\VoucherCodeInvalidArgumentException;
use App\Shop\VoucherCodes\Exceptions\VoucherCodeNotFoundException;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\VoucherCodes\Transformations\VoucherCodeTransformable;
use App\Shop\Base\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use App\Shop\Channels\Channel;
use Illuminate\Support\Facades\DB;

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
        return $this->model->delete();
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
                    'use_count' => 10,
                    'voucher_code' => 10,
                    'voucher_id' => 10
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
     * @return boolean
     */
    public function validateVoucherCode(Channel $channel, string $voucherCode, $cartProducts) {

        $results = DB::select(DB::raw("SELECT * 
                                        FROM voucher_codes vc
                                        INNER JOIN vouchers v ON v.id = vc.voucher_id

                                       WHERE voucher_code = :code

                                         AND (use_count > 0)

                                         AND (expiry_date IS NULL or expiry_date > NOW()) 
                                         AND (NOW() >= start_date)

                                         AND channel = :channel"), ['channel' => $channel->id, 'code' => $voucherCode]);



        if (empty($results)) {
            $this->validationFailures[] = 'unable to find voucher code';
            return false;
        }

        if (!$this->validateVoucherScopes($results, $cartProducts)) {
            $this->validationFailures[] = 'unable to validate voucher code';
            return false;
        }

        request()->session()->put('voucherCode', $results[0]->voucher_code);

        return true;
    }

    /**
     * 
     * @param type $results
     * @param type $cartProducts
     * @return boolean
     */
    private function validateVoucherScopes($results, $cartProducts) {
        $scopeType = $results[0]->scope_type;
        $scopeValue = (int) $results[0]->scope_value;

        foreach ($cartProducts as $cartProduct) {

            switch ($scopeType) {

                case 'Brand':
                    if (empty($cartProduct->product->brand_id)) {

                        return false;
                    }

                    if ((int) $cartProduct->product->brand_id !== $scopeValue) {

                        return false;
                    }

                    break;

                case 'Product':

                    if (empty($cartProduct->product->id)) {

                        return false;
                    }

                    if ((int) $cartProduct->product->id !== $scopeValue) {

                        return false;
                    }

                    break;

                case 'Category':

                    $categoryIds = $cartProduct->product->categories()->pluck('category_id')->all();

                    if (!in_array($scopeValue, $categoryIds)) {

                        return false;
                    }
            }
        }

        return true;
    }
    
    public function getByVoucherCode($voucherCode) {
        
        try {
            return VoucherCode::where('voucher_code', $voucherCode)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new VoucherCodeNotFoundException($e->getMessage());
        }
        
    }

}

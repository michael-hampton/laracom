<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Shop\Vouchers;

use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\VoucherCodes\VoucherCode;
use Illuminate\Support\Facades\DB;

/**
 * Description of VoucherGenerator
 *
 * @author michael.hampton
 */
class VoucherGenerator {

    /**
     *
     * @var type 
     */
    private $characters;

    /**
     *
     * @var type 
     */
    private $mask;

    /**
     *
     * @var type 
     */
    private $prefix;

    /**
     *
     * @var type 
     */
    private $suffix;

    /**
     *
     * @var type 
     */
    private $separator = '-';

    /**
     *
     * @var type 
     */
    private $generatedCodes = [];

    /**
     * 
     * @param string $characters
     * @param string $mask
     */
    public function __construct(string $characters = 'ABCDEFGHJKLMNOPQRSTUVWXYZ234567890', string $mask = '****-****') {
        $this->characters = $characters;
        $this->mask = $mask;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void {
        $this->prefix = $prefix;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix(string $suffix): void {
        $this->suffix = $suffix;
    }

    /**
     * @param string $separator
     */
    public function setSeparator(string $separator): void {
        $this->separator = $separator;
    }

    /**
     * Create the voucher
     *
     * @param array $params
     * @return Address
     */
    public function createVoucher(Voucher $voucher, int $use_count, int $quantity): bool {
        try {

            $voucherCodes = $this->generate($quantity);

            foreach ($voucherCodes as $voucherCode) {

                $voucher_code = new VoucherCode(array(
                    'voucher_code' => $voucherCode,
                    'use_count' => $use_count,
                    'status' => 1,
                    'voucher_id' => $voucher->id
                        )
                );
                $voucher_code->save();
            }
        } catch (QueryException $e) {

            return false;
        }

        return true;
    }

    /**
     * Generate the specified amount of codes and return
     * an array with all the generated codes.
     *
     * @param int $amount
     * @return array
     */
    private function generate(int $amount = 1): array {
        $codes = [];
        for ($i = 1; $i <= $amount; $i++) {

            $codes[] = $this->getUniqueVoucher();
        }
        return $codes;
    }

    /**
     * @return string
     */
    private function getUniqueVoucher(): string {

        $voucher = $this->generateRandomString();

        while (!empty($this->checkVoucherCodeExists($voucher))) {
            $voucher = $this->generateRandomString();
        }

        return $voucher;
    }

    /**
     * 
     * @param type $voucher
     * @return type
     */
    private function checkVoucherCodeExists($voucher) {

        return DB::select('select * from voucher_codes where voucher_code = ?', [$voucher]);
    }

    public function generateRandomString() {
        $length = substr_count($this->mask, '*');
        $code = $this->getPrefix();
        $mask = $this->mask;
        $characters = collect(str_split($this->characters));
        for ($i = 0; $i < $length; $i++) {
            $mask = str_replace_first('*', $characters->random(1)->first(), $mask);
        }
        $code .= $mask;
        $code .= $this->getSuffix();

        return $code;
    }

    /**
     * @return string
     */
    private function getPrefix(): string {
        return $this->prefix !== null ? $this->prefix . $this->separator : '';
    }

    /**
     * @return string
     */
    private function getSuffix(): string {
        return $this->suffix !== null ? $this->separator . $this->suffix : '';
    }

}

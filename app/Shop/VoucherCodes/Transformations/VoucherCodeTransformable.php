<?php

namespace App\Shop\VoucherCodes\Transformations;

use App\Shop\VoucherCodes\VoucherCode;
use App\Shop\Vouchers\Repositories\VoucherRepository;
use App\Shop\Vouchers\Voucher;

trait VoucherCodeTransformable {

    /**
     * Transform the voucher
     *
     * @param Voucher $voucher
     * @return Voucher
     */
    public function transformVoucherCode(VoucherCode $voucherCode) {
        
        $voucher = new VoucherRepository(new Voucher);
        
        $objVoucher = $voucher->findVoucherById($voucherCode->voucher_id);
        
        $obj = new VoucherCode;
        $obj->id = $voucherCode->id;
        $obj->use_count = $voucherCode->use_count;
        $obj->voucher_code = $voucherCode->voucher_code;
        $obj->status = $voucherCode->status;
        $obj->voucher_id = $voucherCode->voucher_id;

        return $obj;
    }

}

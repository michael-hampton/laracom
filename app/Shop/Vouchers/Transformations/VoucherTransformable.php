<?php

namespace App\Shop\Vouchers\Transformations;

use App\Shop\Vouchers\Voucher;

trait VoucherTransformable {

    /**
     * Transform the voucher
     *
     * @param Voucher $voucher
     * @return Voucher
     */
    public function transformVoucher(\App\Shop\Vouchers\Voucher $voucher) {
        $obj = new Voucher;
        $obj->id = $voucher->id;
        $obj->coupon_code = $voucher->coupon_code;
        $obj->amount = $voucher->amount;
        $obj->amount_type = $voucher->amount_type;
        $obj->expiry_date = $voucher->expiry_date;
        $obj->start_date = $voucher->start_date;
        $obj->status = $voucher->status;
        $obj->channel = $voucher->channel;

        return $obj;
    }

}

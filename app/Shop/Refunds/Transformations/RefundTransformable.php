<?php

namespace App\Shop\Refunds\Transformations;

use App\Shop\Refunds\Refund;

trait RefundTransformable {

    /**
     * Transform the refund
     *
     * @param Refund $refund
     * @return Refund
     */
    public function transformRefund(Refund $refund) {
        $obj = new Refund;
        $obj->id = $refund->id;
        $obj->order_id = $refund->order_id;
        $obj->quantity = $refund->quantity;
        $obj->amount = $refund->amount;
        $obj->date_refunded = $refund->date_refunded;

        return $obj;
    }

}

<?php

namespace App\Shop\Returns\Transformations;

use App\Shop\Returns\Returns;

trait ReturnLineTransformable {

    /**
     * Transform the refund
     *
     * @param Returns $return
     * @return Returns
     */
    public function transformRefundLine(Returns $return) {
        $obj = new Returns;
        $obj->id = $return->id;


        return $obj;
    }

}

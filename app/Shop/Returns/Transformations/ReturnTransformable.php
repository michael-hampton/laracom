<?php

namespace App\Shop\Returns\Transformations;

use App\Shop\Returns\Returns;

trait ReturnTransformable {

    /**
     * Transform the refund
     *
     * @param Returns $return
     * @return Returns
     */
    public function transformReturn(Returns $return) {

        $obj = new Returns;
        $obj->id = $return->id;
        $obj->created_at = $return->created_at;
        $obj->order_id = $return->order_id;
        $obj->item_condition = $return->item_condition;
        $obj->resolution = $return->resolution;


        return $obj;
    }

}

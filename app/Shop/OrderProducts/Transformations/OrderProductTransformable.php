<?php

namespace App\Shop\OrderProducts\Transformations;

use App\Shop\OrderProducts\OrderProduct;
use Illuminate\Support\Facades\Storage;

trait OrderProductTransformable {

    /**
     * Transform the channel
     *
     * @param Channel $channel
     * @return Channel
     */
    protected function transformOrderProduct(OrderProduct $orderProduct) {
        $orderProductObj = new OrderProduct;
        $orderProductObj->id = (int) $orderProduct->id;
        $orderProductObj->product_id = (int) $orderProduct->product_id;
        $orderProductObj->order_id = (int) $orderProduct->order_id;
        $orderProductObj->quantity = $orderProduct->quantity;
        $orderProductObj->product_name = $orderProduct->product_name;
        $orderProductObj->product_sku = $orderProduct->product_sku;
        $orderProductObj->product_description = $orderProduct->product_description;
        $orderProductObj->product_price = $orderProduct->product_price;
        $orderProductObj->price = $orderProduct->product_price;
        $orderProductObj->status = $orderProduct->status;
        $orderProductObj->created_at = $orderProduct->created_at;
        $orderProductObj->tote = $orderProduct->tote;
        $orderProductObj->sage_ref = $orderProduct->sage_ref;
        $orderProductObj->picklist_ref = $orderProduct->picklist_ref;
        $orderProductObj->warehouse = $orderProduct->warehouse;
        $orderProductObj->courier_id = (int) $orderProduct->courier_id;
        $orderProductObj->tracking_code = $orderProduct->tracking_code;

        return $orderProductObj;
    }

}

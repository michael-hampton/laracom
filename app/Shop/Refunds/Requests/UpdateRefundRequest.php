<?php

namespace App\Shop\Refunds\Requests;

use App\Shop\Base\BaseFormRequest;

class UpdateRefundRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'order_id' => ['required'],
            'quantity' => ['required'],
            'amount' => ['required'],
            'date_refunded' => ['required'],
            'status' => ['required']
            
        ];
    }

}

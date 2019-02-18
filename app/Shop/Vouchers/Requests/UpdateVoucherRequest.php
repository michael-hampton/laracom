<?php

namespace App\Shop\Vouchers\Requests;

use App\Shop\Base\BaseFormRequest;

class UpdateVoucherRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'name' => ['required'],
            'amount' => ['required'],
            'amount_type' => ['required'],
            'start_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date'],
            'status' => ['required'],
            'channel' => ['required'],
            'scope_type' => ['required'],
            'scope_value' => ['required']
        ];
    }

}

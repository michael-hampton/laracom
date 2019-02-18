<?php

namespace App\Shop\Vouchers\Requests;

use App\Shop\Base\BaseFormRequest;

class CreateVoucherRequest extends BaseFormRequest {

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
            'expiry_date' => ['required', 'date'],
            'start_date' => ['required', 'date'],
            'status' => ['required'],
            'scope_type' => ['required']
        ];
    }

}

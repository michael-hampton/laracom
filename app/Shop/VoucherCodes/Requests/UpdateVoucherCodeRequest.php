<?php

namespace App\Shop\VoucherCodes\Requests;

use App\Shop\Base\BaseFormRequest;

class UpdateVoucherCodeRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'use_count' => ['required', 'numeric'],
            'voucher_id' => ['required'],
            'status' => ['required', 'numeric']
        ];
    }

}

<?php

namespace App\Shop\Returns\Requests;

use App\Shop\Base\BaseFormRequest;

class UpdateReturnRequest extends BaseFormRequest {

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'item_condition' => ['required'],
            'resolution' => ['required'],
            'lines' => ['required']
        ];
    }

}

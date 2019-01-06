<?php

namespace App\Shop\Orders\Requests;

use App\Shop\Base\BaseFormRequest;

class ImportRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'channel' => ['required'],
            'customer_id' => ['required'],
            'shipping' => ['required'],
            'total' => ['required']
        ];
    }
}

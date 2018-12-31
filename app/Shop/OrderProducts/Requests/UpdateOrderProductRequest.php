<?php

namespace App\Shop\OrderProducts\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderProductRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'lineId' => ['required'],
            'orderId' => ['required'],
            'quantity' => ['required'],
            'productId' => ['required']
        ];
    }

}

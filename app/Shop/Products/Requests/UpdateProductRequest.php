<?php

namespace App\Shop\Products\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest {

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
        $id = request()->post('id', 1);

        return [
            'sku' => ['required'],
            'name' => 'unique:products,name,' . $id,
            'quantity' => ['required', 'numeric'],
            'price' => ['required']
        ];
    }

}

<?php

namespace App\Shop\Couriers\Requests;

use App\Shop\Base\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', Rule::unique('couriers')->ignore($this->segment(3))],
            'range_from' => ['required'],
            'range_to' => ['required'],
            'country' => ['required'],
        ];
    }
}

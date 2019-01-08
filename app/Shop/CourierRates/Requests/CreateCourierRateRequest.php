<?php
namespace App\Shop\CourierRates\Requests;
use App\Shop\Base\BaseFormRequest;
class CreateCourierRateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'courier' => ['required'],
            'cost' => ['required'],
            'range_from' => ['required'],
            'range_to' => ['required'],
            'country' => ['required'],
        ];
    }
}

<?php
namespace App\Shop\Orders\Requests;
use App\Shop\Base\BaseFormRequest;
class NewOrderRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer' => ['required'],
            'channel' => ['required']
        ];
    }
}

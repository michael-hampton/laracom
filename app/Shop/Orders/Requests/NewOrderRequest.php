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
            'reference' => ['required'],
            'courier_id' => ['required'],
            'customer_id' => ['required'],
            'address_id' => ['required'],
            'order_status_id' => ['required'],
            'payment' => ['required'],
            'total' => ['required']
        ];
    }
}

<?php
namespace App\Shop\Refunds\Requests;
use App\Shop\Base\BaseFormRequest;
class CreateReturnRequest extends BaseFormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'order_id' => ['required'],
            'quantity' => ['required'],
            'amount' => ['required'],
            'status' => ['required']
        ];
    }
}

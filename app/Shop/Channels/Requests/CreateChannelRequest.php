<?php

namespace App\Shop\Channels\Requests;

use App\Shop\Base\BaseFormRequest;

class CreateChannelRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'unique:products'],
            'allocate_on_order' => ['required'],
            'backorders_enabled' => ['required'],
            'has_priority' => ['required'],
            'cover' => ['file', 'image:png,jpeg,jpg,gif']
        ];
    }
}

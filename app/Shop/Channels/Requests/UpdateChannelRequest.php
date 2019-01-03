<?php

namespace App\Shop\Channels\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChannelRequest extends FormRequest {

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
            'name' => ['required', 'unique:products'],
            //'allocate_on_order' => ['required'],
            //'backorders_enabled' => ['required'],
            //'has_priority' => ['required'],
            'cover' => ['file', 'image:png,jpeg,jpg,gif']
        ];
    }

}

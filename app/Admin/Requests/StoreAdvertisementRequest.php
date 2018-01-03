<?php

namespace App\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvertisementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
//            'loading_page' => 'required|max:255',
            'click_track_url' => 'required|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '广告标题不能为空',
            'name.max' => '广告标题不能超过255个字符',
            'loading_page.required' => '落地页不能为空',
            'loading_page.max' => '落地页不能为空',
            'click_track_url.required' => '广告上报地址不能为空',
            'click_track_url.max' => '广告上报地址不能为空',
        ];
    }
}

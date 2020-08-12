<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReminderRequest extends FormRequest
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
            'label_id' => 'required',
            'title' => 'required',
            'days_before' => 'required',
            'time' => 'required',
            'tone' => 'required'
        ];
    }
}

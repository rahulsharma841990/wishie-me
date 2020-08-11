<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileUpdateRequest extends FormRequest
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
        $userId = Auth::user()->id;
        $rulesArray = [];
        if(request()->has('email')){
            $rulesArray['email'] = 'unique:users,email,'.$userId;
        }
        if(request()->has('phone')){
            $rulesArray['phone'] = 'unique:users,phone,'.$userId;
        }
        return $rulesArray;
    }
}

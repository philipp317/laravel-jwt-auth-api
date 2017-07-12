<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class RegisterRequest extends Request
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
            'name' => 'required|min:3|max:20|string',
            'phone' => 'required_without:google,facebook|unique:users,phone|numeric|digits_between:7,15',
            'email' => 'required_without:google,facebook|email|unique:users,email',
            'password' => 'required_without:google,facebook|min:6'
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class UpdateProfile extends FormRequest
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
            'name' => 'nullable',
            'position' => 'nullable',
            'phone_number' => 'nullable',
            'email' => 'nullable|email:rfc,dns',
            'password' => 'required',
            'new-password' => 'nullable|string|min:8|required_with:new-password_confirmation|confirmed',
            'business-name' => 'nullable',
            'url' => 'nullable',
            'category' => 'nullable',
            'payment-provider' => 'nullable',
            'street_address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'zipcode' => 'nullable',
            'summary' => 'nullable',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        // checks user current password
        // before making changes
        $validator->after(function ($validator) {
            if (!Hash::check($this->password, $this->user()->password)) {
                $validator->errors()->add('password', 'Your current password is incorrect.');
            }
        });
        return;
    }
}

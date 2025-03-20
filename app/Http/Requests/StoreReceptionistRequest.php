<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceptionistRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only an admin should be allowed to create a receptionist
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|min:3|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6|confirmed',
            'national_id'  => 'required|string|unique:employees,national_id',
            'avatar_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}

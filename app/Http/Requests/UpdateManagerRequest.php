<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $managerId = $this->route('manager')->id;
        $employeeId = optional($this->route('manager')->profile)->id;
        return [
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email,' . $managerId,
            'password' => 'nullable|string|min:6|confirmed',
            'national_id' => 'required|string|unique:employees,national_id,' . $employeeId,
            'avatar_image' => 'nullable|image|mimes:jpg,jpeg|max:2048',
        ];
    }
}

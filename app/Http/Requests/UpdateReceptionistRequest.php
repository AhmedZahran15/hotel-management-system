<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReceptionistRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only an admin should be allowed to update a receptionist
        return auth()->user()->hasRole(['admin', 'manager']);
    }

    public function rules(): array
    {
        // Get the ID of the receptionist user and its associated employee record
        $receptionistId = $this->route('receptionist')->id;
        $employeeId = optional($this->route('receptionist')->profile)->id;

        return [
            'name'         => 'required|string|min:3|max:255',
            'email'        => 'required|email|unique:users,email,' . $receptionistId,
            'password'     => 'nullable|string|min:6|confirmed',
            'national_id'  => 'required|string|unique:employees,national_id,' . $employeeId,
            'avatar_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}

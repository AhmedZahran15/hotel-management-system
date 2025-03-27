<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roomNumber = $this->route('room')->number ?? null;

        return [
            'floor_number' => ['required','integer'],
            'number' => [
                'required','integer','min:1000',
                Rule::unique('rooms','number')->ignore($roomNumber, 'number')
            ],
            'capacity' => ['required','integer','max:5'],
            'room_price' => ['required','integer'],
            'state' => ['required','in:available,occupied,being_reserved,maintenance'],
        ];
    }
}

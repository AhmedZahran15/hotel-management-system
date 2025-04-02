<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class StoreReservationRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $room = Room::findOrFail($this->room_number);

        return [
            'room_number' => 'required|exists:rooms,number',
            'accompany_number' => "required|integer|min:1|max:{$room->capacity}",
            'payment_method_id' => 'required|string',
            // Add these fields to the validation rules
            'return_url' => 'nullable|string|url',
            'automatic_payment_methods' => 'nullable|boolean',
        ];
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all($keys = null)
    {
        $data = parent::all($keys);
        // Ensure boolean value is correctly transmitted
        if (isset($data['automatic_payment_methods'])) {
            $data['automatic_payment_methods'] = filter_var($data['automatic_payment_methods'], FILTER_VALIDATE_BOOLEAN);
        }
        return $data;
    }
}

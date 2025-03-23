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

    public function rules()
    {
        return [
            'reservation_date'  => 'required|date',
            'reservation_price' => 'required|integer|min:1',
            'accompany_number'  => ['required', 'integer', function($attribute, $value, $fail) {
                $room = Room::findOrFail($this->route('room'));
                if($value > $room->capacity){
                    $fail('The number of accompanying guests exceeds the room capacity.');
                }
            }],
        ];
    }
}

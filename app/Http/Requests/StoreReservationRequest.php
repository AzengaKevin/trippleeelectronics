<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guests' => ['required', 'array', 'min:1'],
            'guests.*.id' => ['required', 'string'],
            'guests.*.email' => ['nullable', 'email', 'max:255'],
            'guests.*.phone' => ['required', 'max:255'],
            'guests.*.address' => ['nullable', 'max:255'],
            'guests.*.kra_pin' => ['nullable', 'max:255'],
            'guests.*.id_number' => ['nullable', 'max:255'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.room' => ['required', Rule::exists(Room::class, 'id')],
            'allocations.*.start' => ['required', 'date'],
            'allocations.*.end' => ['required', 'date'],
            'allocations.*.occupancy' => ['nullable', 'numeric'],
            'allocations.*.price' => ['nullable', 'numeric'],
            'allocations.*.discount' => ['nullable', 'numeric'],
            'allocations.*.amount' => ['nullable', 'numeric'],
            'total_amount' => ['nullable', 'numeric'],
            'checkin_date' => ['required', 'date'],
            'checkout_date' => ['required', 'date'],
            'guests_count' => ['nullable', 'numeric'],
            'rooms_count' => ['nullable', 'numeric'],
            'tendered_amount' => ['nullable', 'numeric'],
            'balance_amount' => ['nullable', 'numeric'],
        ];
    }
}

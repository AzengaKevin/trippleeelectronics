<?php

namespace App\Http\Requests;

use App\Models\Building;
use App\Models\RoomType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'building' => ['nullable', Rule::exists(Building::class, 'id')],
            'room_type' => ['nullable', Rule::exists(RoomType::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'occupancy' => ['nullable', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:255'],
        ];
    }
}

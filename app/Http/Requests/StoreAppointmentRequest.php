<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isPetOwner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'veterinarian_id' => ['required', 'exists:veterinarians,id'],
            'pet_id' => ['required', 'exists:pets,id'],
            'time_slot_id' => ['required', 'exists:time_slots,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'veterinarian_id.required' => 'Please select a veterinarian.',
            'veterinarian_id.exists' => 'The selected veterinarian does not exist.',
            'pet_id.required' => 'Please select a pet for this appointment.',
            'pet_id.exists' => 'The selected pet does not exist.',
            'time_slot_id.required' => 'Please select a time slot.',
            'time_slot_id.exists' => 'The selected time slot does not exist.',
        ];
    }
}

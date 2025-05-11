<?php

namespace App\Http\Requests\SeekerProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEducationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seeker_id' => 'required|exists:jobseekers,seeker_id',
            'university' => 'required|string|max:255',
            'college' => 'required|string|max:255',
            'start_date' => 'required|string',
            'end_date' => 'required|string',
        ];
    }
}

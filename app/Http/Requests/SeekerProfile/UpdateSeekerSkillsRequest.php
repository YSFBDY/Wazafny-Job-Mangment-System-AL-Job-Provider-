<?php

namespace App\Http\Requests\SeekerProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeekerSkillsRequest extends FormRequest
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
            'seeker_id' => 'required|integer',
            'skills' => 'array',
            'skills.*' => 'required|max:255'
        ];
    }
}

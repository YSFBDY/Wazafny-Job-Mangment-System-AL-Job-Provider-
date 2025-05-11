<?php

namespace App\Http\Requests\SeekerProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalInfoRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'headline' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'links' => 'nullable|array',
            'links.*.link_name' => 'required_with:links|string|max:255',
            'links.*.link' => 'required_with:links|url'
        ];
    }
}

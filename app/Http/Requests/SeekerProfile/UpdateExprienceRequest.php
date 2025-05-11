<?php

namespace App\Http\Requests\SeekerProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExprienceRequest extends FormRequest
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
            'experience_id' => 'required|integer',
            'company' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'job_time' => 'required|string|max:255',
            'start_date' => 'required|string|max:255',
            'end_date' => 'required|string|max:255',
        ];
    }
}

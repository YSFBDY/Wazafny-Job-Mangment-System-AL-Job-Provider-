<?php

namespace App\Http\Requests\JobPost;

use Illuminate\Foundation\Http\FormRequest;

class CreateJobPostRequest extends FormRequest
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
            'job_title' => 'required|string',
            'job_about' => 'required|string',
            'job_time' => 'required|string|in:Full-time,Part-time',
            'job_type' => 'required|string|in:On-site,Remote',
            'job_country' => 'required|string',
            'job_city' => 'required|string',
            'company_id' => 'required|integer',

            'skills' => 'array',
            'skills.*' => 'required|max:255', 

            'questions' => 'array',
            'questions.*' => 'required', 

            'sections' => 'required|array',
            'sections.*' => 'required', 
        ];
        
    }
}

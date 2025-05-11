<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;

class CreateAppicationRequest extends FormRequest
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
        $jobId = $this->input('job_id');
        $hasQuestions = \App\Models\Question::where('job_id', $jobId)->exists();
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'seeker_id' => 'required|exists:jobseekers,seeker_id',
            'job_id' => 'required|integer|exists:jobposts,job_id',
            'answers' => $hasQuestions ? 'required|array' : 'nullable|array',
            'answers.*' => $hasQuestions ? 'required|string' : 'nullable|string',
        ];
    }
}

<?php

namespace App\Http\Requests\CompanyProfile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExtraInfoRequest extends FormRequest
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
            'company_id' => 'required|integer',
            'about' => 'required|string',
            'company_industry' => 'required|string|max:255',
            'company_size' => 'required|string|max:255',
            'company_heads' => 'nullable|string|max:255',
            'company_country' => 'required|string|max:100',
            'company_city' => 'required|string|max:100',
            'company_founded' => 'required|date_format:Y'
        ];
    }
}

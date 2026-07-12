<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CvStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'desired_position' => 'nullable|string|max:255',
            'motivation' => 'nullable|string',
            'histories' => 'nullable|array',
            'histories.*.company_name' => 'nullable|string|max:255',
            'histories.*.start_year' => 'nullable|integer',
            'histories.*.start_month' => 'nullable|integer',
            'histories.*.end_year' => 'nullable|integer',
            'histories.*.end_month' => 'nullable|integer',
            'histories.*.job_description' => 'nullable|string',
            'licenses' => 'nullable|array',
            'licenses.*.year' => 'nullable|integer',
            'licenses.*.month' => 'nullable|integer',
            'licenses.*.name' => 'nullable|string|max:255',
        ];
    }
}

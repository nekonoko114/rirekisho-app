<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class CvRequest extends FormRequest
{
    /**
     * Build shared validation rules for CV store and update requests.
     *
     * @return array<string, mixed>
     */
    protected function cvBaseRules(bool $includeChildIds = false): array
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'desired_position' => 'nullable|string|max:255',
            'motivation' => 'nullable|string|max:3000',
            'histories' => 'nullable|array|max:50',
            'histories.*.company_name' => 'nullable|string|max:255',
            'histories.*.start_year' => 'nullable|integer|min:1900|max:2100',
            'histories.*.start_month' => 'nullable|integer|min:1|max:12',
            'histories.*.end_year' => 'nullable|integer|min:1900|max:2100',
            'histories.*.end_month' => 'nullable|integer|min:1|max:12',
            'histories.*.job_description' => 'nullable|string|max:3000',
            'licenses' => 'nullable|array|max:50',
            'licenses.*.year' => 'nullable|integer|min:1900|max:2100',
            'licenses.*.month' => 'nullable|integer|min:1|max:12',
            'licenses.*.name' => 'nullable|string|max:255',
        ];

        if ($includeChildIds) {
            $rules['histories.*.id'] = 'nullable|exists:cv_histories,id';
            $rules['licenses.*.id'] = 'nullable|exists:cv_licenses,id';
        }

        return $rules;
    }
}

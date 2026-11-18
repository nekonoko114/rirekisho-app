<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ResumeRequest extends FormRequest
{
    /**
     * Split fields that should be combined with hyphens when JS is disabled.
     *
     * @var array<string, int>
     */
    private const HYPHENATED_PART_COUNTS = [
        'phone' => 3,
        'contact_phone' => 3,
        'address_postal' => 2,
        'contact_postal' => 2,
    ];

    /**
     * Build shared validation rules for resume store and update requests.
     *
     * @return array<string, mixed>
     */
    protected function resumeBaseRules(bool $includeChildIds = false): array
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'furigana' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9-]+$/'],
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'address_postal' => ['nullable', 'string', 'max:20', 'regex:/^\d{3}-\d{4}$/'],
            'contact_address' => 'nullable|string|max:500',
            'contact_postal' => ['nullable', 'string', 'max:20', 'regex:/^\d{3}-\d{4}$/'],
            'contact_phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9-]+$/'],
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048|dimensions:max_width=4096,max_height=4096',
            'licenses' => 'nullable|array|max:50',
            'licenses.*.year' => 'nullable|integer|min:1900|max:2100',
            'licenses.*.month' => 'nullable|integer|min:1|max:12',
            'licenses.*.name' => 'nullable|string|max:255',
            'licenses.*.details' => 'nullable|string|max:500',
            'licenses_text' => 'nullable|string|max:5000',
            'motivation' => 'nullable|string|max:3000',
            'personal_requests' => 'nullable|string|max:3000',
        ];

        if ($includeChildIds) {
            $rules['licenses.*.id'] = 'nullable|integer|exists:resume_licenses,id';
        }

        foreach (['histories', 'histories_education', 'histories_work'] as $historyKey) {
            $rules = array_merge($rules, $this->historyGroupRules($historyKey, $includeChildIds));
        }

        return $rules;
    }

    /**
     * Build validation rules for a resume history array input.
     *
     * @return array<string, string>
     */
    private function historyGroupRules(string $key, bool $includeId): array
    {
        $rules = [
            $key => 'nullable|array|max:50',
            "{$key}.*.year" => 'nullable|integer|min:1900|max:2100',
            "{$key}.*.month" => 'nullable|integer|min:1|max:12',
            "{$key}.*.type" => 'nullable|string|in:education,work',
            "{$key}.*.description" => 'nullable|string|max:500',
        ];

        if ($includeId) {
            $rules["{$key}.*.id"] = 'nullable|integer|exists:resume_histories,id';
        }

        return $rules;
    }

    /**
     * Combine split phone/postal part inputs when client-side JS did not populate the combined field.
     */
    protected function prepareForValidation(): void
    {
        foreach (self::HYPHENATED_PART_COUNTS as $field => $partCount) {
            $this->combineHyphenatedParts($field, $partCount);
        }
    }

    private function combineHyphenatedParts(string $field, int $partCount): void
    {
        if (! empty($this->input($field))) {
            return;
        }

        $parts = [];
        for ($i = 1; $i <= $partCount; $i++) {
            $value = trim((string) $this->input("{$field}_part{$i}", ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        if ($parts !== []) {
            $this->merge([$field => implode('-', $parts)]);
        }
    }
}

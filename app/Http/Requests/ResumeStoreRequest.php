<?php

namespace App\Http\Requests;

class ResumeStoreRequest extends ResumeRequest
{
    public function authorize(): bool
    {
        // Allow both authenticated users and guests to create resumes.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->resumeBaseRules(includeChildIds: false), [
            'histories_education_text' => 'nullable|string|max:5000',
            'histories_work_text' => 'nullable|string|max:5000',
        ]);
    }
}

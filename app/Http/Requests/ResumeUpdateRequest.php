<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesOwnedRouteResource;

class ResumeUpdateRequest extends ResumeRequest
{
    use AuthorizesOwnedRouteResource;

    public function authorize(): bool
    {
        return $this->authorizeOwnedRouteResource('resume');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->resumeBaseRules(includeChildIds: true);
    }
}

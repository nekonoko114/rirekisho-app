<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesOwnedRouteResource;

class CvUpdateRequest extends CvRequest
{
    use AuthorizesOwnedRouteResource;

    public function authorize(): bool
    {
        return $this->authorizeOwnedRouteResource('cv');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->cvBaseRules(includeChildIds: true);
    }
}

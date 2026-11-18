<?php

namespace App\Http\Requests;

class CvStoreRequest extends CvRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->cvBaseRules(includeChildIds: false);
    }
}

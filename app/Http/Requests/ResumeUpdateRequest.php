<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $resume = $this->route('resume');
        $user = $this->user();
        if (! $user || ! $resume) {
            return false;
        }

        $isOwner = ($resume->user_id && $resume->user_id === $user->id);
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin());

        return $isOwner || $isAdmin;
    }

    public function rules()
    {
        return [
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
            'histories' => 'nullable|array|max:50',
            'histories.*.id' => 'nullable|integer|exists:resume_histories,id',
            'histories.*.year' => 'nullable|integer|min:1900|max:2100',
            'histories.*.month' => 'nullable|integer|min:1|max:12',
            'histories.*.type' => 'nullable|string|in:education,work',
            'histories.*.description' => 'nullable|string|max:500',
            'histories_education' => 'nullable|array|max:50',
            'histories_education.*.id' => 'nullable|integer|exists:resume_histories,id',
            'histories_education.*.year' => 'nullable|integer|min:1900|max:2100',
            'histories_education.*.month' => 'nullable|integer|min:1|max:12',
            'histories_education.*.type' => 'nullable|string|in:education,work',
            'histories_education.*.description' => 'nullable|string|max:500',
            'histories_work' => 'nullable|array|max:50',
            'histories_work.*.id' => 'nullable|integer|exists:resume_histories,id',
            'histories_work.*.year' => 'nullable|integer|min:1900|max:2100',
            'histories_work.*.month' => 'nullable|integer|min:1|max:12',
            'histories_work.*.type' => 'nullable|string|in:education,work',
            'histories_work.*.description' => 'nullable|string|max:500',
            'licenses' => 'nullable|array|max:50',
            'licenses.*.id' => 'nullable|integer|exists:resume_licenses,id',
            'licenses.*.year' => 'nullable|integer|min:1900|max:2100',
            'licenses.*.month' => 'nullable|integer|min:1|max:12',
            'licenses.*.name' => 'nullable|string|max:255',
            'licenses.*.details' => 'nullable|string|max:500',
            'licenses_text' => 'nullable|string|max:5000',
            'motivation' => 'nullable|string|max:3000',
            'personal_requests' => 'nullable|string|max:3000',
        ];
    }

    protected function prepareForValidation()
    {
        // Same combination logic as store
        $phone = $this->input('phone');
        if (empty($phone)) {
            $p1 = $this->input('phone_part1', '');
            $p2 = $this->input('phone_part2', '');
            $p3 = $this->input('phone_part3', '');
            $parts = array_filter([$p1, $p2, $p3], function ($v) {
                return trim((string) $v) !== '';
            });
            if (! empty($parts)) {
                $this->merge(['phone' => implode('-', $parts)]);
            }
        }

        $contactPhone = $this->input('contact_phone');
        if (empty($contactPhone)) {
            $cp1 = $this->input('contact_phone_part1', '');
            $cp2 = $this->input('contact_phone_part2', '');
            $cp3 = $this->input('contact_phone_part3', '');
            $cparts = array_filter([$cp1, $cp2, $cp3], function ($v) {
                return trim((string) $v) !== '';
            });
            if (! empty($cparts)) {
                $this->merge(['contact_phone' => implode('-', $cparts)]);
            }
        }

        $addressPostal = $this->input('address_postal');
        if (empty($addressPostal)) {
            $ap1 = $this->input('address_postal_part1', '');
            $ap2 = $this->input('address_postal_part2', '');
            $aparts = array_filter([$ap1, $ap2], function ($v) {
                return trim((string) $v) !== '';
            });
            if (! empty($aparts)) {
                $this->merge(['address_postal' => implode('-', $aparts)]);
            }
        }

        $contactPostal = $this->input('contact_postal');
        if (empty($contactPostal)) {
            $cap1 = $this->input('contact_postal_part1', '');
            $cap2 = $this->input('contact_postal_part2', '');
            $caparts = array_filter([$cap1, $cap2], function ($v) {
                return trim((string) $v) !== '';
            });
            if (! empty($caparts)) {
                $this->merge(['contact_postal' => implode('-', $caparts)]);
            }
        }
    }
}

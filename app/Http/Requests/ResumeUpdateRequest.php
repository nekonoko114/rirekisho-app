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
            'address' => 'nullable|string',
            'address_postal' => ['nullable', 'string', 'max:20', 'regex:/^\d{3}-\d{4}$/'],
            'contact_address' => 'nullable|string',
            'contact_postal' => ['nullable', 'string', 'max:20', 'regex:/^\d{3}-\d{4}$/'],
            'contact_phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9-]+$/'],
            'photo' => 'nullable|image|max:2048',
            'histories' => 'nullable|array',
            'histories_education' => 'nullable|array',
            'histories_work' => 'nullable|array',
            'licenses' => 'nullable|array',
            'motivation' => 'nullable|string',
            'personal_requests' => 'nullable|string',
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

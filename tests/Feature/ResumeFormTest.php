<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Resume;

class ResumeFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_form_saves_combined_fields()
    {
        // Prepare payload simulating the form (hidden combined fields already set by JS in browser)
        $payload = [
            'name' => 'テスト太郎',
            'phone' => '03-1234-5678',
            'contact_phone' => '090-1111-2222',
            'address' => '東京都千代田区1-1-1',
            'address_postal' => '100-0001',
            'contact_address' => '大阪府大阪市1-2-3',
            'contact_postal' => '530-0001',
            'motivation' => 'テストの志望動機',
            'personal_requests' => '特になし',
        ];

        $response = $this->post(route('resumes.store'), $payload);

        // controller redirects to show on success
        $response->assertStatus(302);

        $this->assertDatabaseHas('resumes', [
            'name' => 'テスト太郎',
            'phone' => '03-1234-5678',
            'contact_phone' => '090-1111-2222',
            'address_postal' => '100-0001',
            'contact_postal' => '530-0001',
        ]);
    }
}

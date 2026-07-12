<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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

    public function test_owner_can_access_edit_page()
    {
        $user = \App\Models\User::factory()->create(['role' => 'user']);
        $resume = \App\Models\Resume::create([
            'user_id' => $user->id,
            'name' => '所有者 太郎',
            'phone' => '03-1234-5678',
            'address' => '東京都千代田区1-1-1',
            'address_postal' => '100-0001',
        ]);

        $response = $this->actingAs($user)->get(route('resumes.edit', $resume));

        $response->assertStatus(200);
    }

    public function test_non_owner_cannot_access_edit_page()
    {
        $owner = \App\Models\User::factory()->create(['role' => 'user']);
        $resume = \App\Models\Resume::create([
            'user_id' => $owner->id,
            'name' => '所有者 太郎',
            'phone' => '03-1234-5678',
            'address' => '東京都千代田区1-1-1',
            'address_postal' => '100-0001',
        ]);

        $nonOwner = \App\Models\User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($nonOwner)->get(route('resumes.edit', $resume));

        $response->assertStatus(403);
    }
}

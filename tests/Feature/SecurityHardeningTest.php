<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\Resume;
use App\Models\User;
use App\Services\ResumePhotoService;
use App\Support\CsvResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_applied_to_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_public_diagnostic_script_is_removed(): void
    {
        $this->assertFileDoesNotExist(public_path('check-pdf-support.php'));
    }

    public function test_csv_response_neutralizes_formula_injection(): void
    {
        $this->assertSame("'=cmd|' /C calc'!A0", CsvResponse::sanitizeCell("=cmd|' /C calc'!A0"));
        $this->assertSame("'+SUM(A1:A2)", CsvResponse::sanitizeCell('+SUM(A1:A2)'));
        $this->assertSame("'-1+1", CsvResponse::sanitizeCell('-1+1'));
        $this->assertSame("'@import", CsvResponse::sanitizeCell('@import'));
        $this->assertSame('通常の文字列', CsvResponse::sanitizeCell('通常の文字列'));

        $user = User::factory()->create(['role' => 'user']);
        Resume::create([
            'user_id' => $user->id,
            'name' => '=HYPERLINK("http://evil.example")',
            'address' => '+12345',
        ]);

        $response = $this->actingAs($user)->get(route('resumes.export'));
        $response->assertStatus(200);

        $csvContent = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csvContent);
        $this->assertStringContainsString("'+12345", $csvContent);
    }

    public function test_oversized_arrays_and_strings_are_rejected_to_prevent_oom(): void
    {
        $tooManyHistories = array_fill(0, 55, [
            'year' => 2020,
            'month' => 4,
            'type' => 'education',
            'description' => 'テスト大学入学',
        ]);

        $response = $this->post(route('resumes.store'), [
            'name' => 'テスト太郎',
            'motivation' => str_repeat('あ', 3500),
            'histories' => $tooManyHistories,
        ]);

        $response->assertSessionHasErrors(['motivation', 'histories']);
    }

    public function test_non_owner_cannot_update_another_users_cv(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);

        $cv = Cv::create([
            'user_id' => $owner->id,
            'name' => '所有者の職務経歴書',
        ]);

        $response = $this->actingAs($other)->put(route('cvs.update', $cv), [
            'name' => '改ざん後の名前',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('cvs', [
            'id' => $cv->id,
            'name' => '所有者の職務経歴書',
        ]);
    }

    public function test_photo_service_delete_rejects_path_traversal(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('important.txt', 'keep-me');
        Storage::disk('public')->put('photos/valid.jpg', 'delete-me');

        $service = new ResumePhotoService;
        $service->delete('important.txt');
        $service->delete('photos/../important.txt');
        $service->delete('photos/valid.jpg');

        Storage::disk('public')->assertExists('important.txt');
        Storage::disk('public')->assertMissing('photos/valid.jpg');
    }

    public function test_admin_cannot_assign_arbitrary_role_or_delete_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $createResponse = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => '不正ロールテスト',
            'email' => 'badrole@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'super_root',
        ]);

        $createResponse->assertSessionHasErrors(['role']);

        $deleteSelfResponse = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin));
        $deleteSelfResponse->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}

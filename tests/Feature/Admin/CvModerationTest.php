<?php

namespace Tests\Feature\Admin;

use App\Models\Cv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CvModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_admin_can_access_cvs_index()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cv = Cv::create(['name' => 'テスト経歴書', 'public_token' => 'token123']);

        $response = $this->actingAs($admin)->get(route('admin.cvs.index'));

        $response->assertStatus(200);
        $response->assertSee('テスト経歴書');
    }

    public function test_non_admin_cannot_access_cvs_index()
    {
        $user = User::factory()->create(['role' => 'user']);
        $response = $this->actingAs($user)->get(route('admin.cvs.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_cvs_index()
    {
        $response = $this->get(route('admin.cvs.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_cv_show()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cv = Cv::create(['name' => 'テスト詳細経歴書', 'public_token' => 'token456']);

        $response = $this->actingAs($admin)->get(route('admin.cvs.show', $cv));

        $response->assertStatus(200);
        $response->assertSee('テスト詳細経歴書');
    }

    public function test_admin_can_delete_cv()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cv = Cv::create(['name' => '削除対象経歴書', 'public_token' => 'token789']);

        $this->assertDatabaseHas('cvs', ['id' => $cv->id]);

        $response = $this->actingAs($admin)->delete(route('admin.cvs.destroy', $cv));

        $response->assertRedirect(route('admin.cvs.index'));
        $this->assertDatabaseMissing('cvs', ['id' => $cv->id]);
    }
}

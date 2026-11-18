<?php

namespace Tests\Feature;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ResumePhotoTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private array $createdPhotoPaths = [];

    protected function tearDown(): void
    {
        foreach ($this->createdPhotoPaths as $relativePath) {
            $fullPath = storage_path('app/public/'.ltrim($relativePath, '/'));
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        parent::tearDown();
    }

    public function test_resume_store_processes_and_stores_portrait_photo(): void
    {
        $user = User::factory()->create();
        $photo = UploadedFile::fake()->image('portrait.jpg', 800, 1000);

        $response = $this->actingAs($user)->post(route('resumes.store'), [
            'name' => '写真テスト太郎',
            'photo' => $photo,
        ]);

        $resume = Resume::firstOrFail();
        $this->assertNotNull($resume->photo_path);
        $this->assertStringStartsWith('photos/', $resume->photo_path);
        $this->createdPhotoPaths[] = $resume->photo_path;

        $response->assertRedirect(route('resumes.show', $resume));

        $storedPath = storage_path('app/public/'.$resume->photo_path);
        $this->assertFileExists($storedPath);

        $imageSize = getimagesize($storedPath);
        $this->assertNotFalse($imageSize);
        $this->assertSame(300, $imageSize[0]);
        $this->assertSame(420, $imageSize[1]);

        $showResponse = $this->actingAs($user)->get(route('resumes.show', $resume));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($resume->photo_path);
    }

    public function test_resume_update_replaces_old_photo_and_destroy_deletes_photo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('resumes.store'), [
            'name' => '写真差し替えテスト',
            'photo' => UploadedFile::fake()->image('first.png', 600, 600),
        ]);

        $resume = Resume::firstOrFail();
        $oldPhotoPath = $resume->photo_path;
        $this->assertNotNull($oldPhotoPath);
        $this->createdPhotoPaths[] = $oldPhotoPath;
        $this->assertFileExists(storage_path('app/public/'.$oldPhotoPath));

        $updateResponse = $this->actingAs($user)->put(route('resumes.update', $resume), [
            'name' => '写真差し替えテスト更新',
            'photo' => UploadedFile::fake()->image('second.webp', 900, 1200),
        ]);

        $updateResponse->assertRedirect(route('resumes.show', $resume));

        $resume->refresh();
        $newPhotoPath = $resume->photo_path;
        $this->assertNotNull($newPhotoPath);
        $this->assertNotSame($oldPhotoPath, $newPhotoPath);
        $this->createdPhotoPaths[] = $newPhotoPath;

        $this->assertFileDoesNotExist(storage_path('app/public/'.$oldPhotoPath));
        $this->assertFileExists(storage_path('app/public/'.$newPhotoPath));

        $deleteResponse = $this->actingAs($user)->delete(route('resumes.destroy', $resume));
        $deleteResponse->assertRedirect(route('resumes.index'));

        $this->assertFileDoesNotExist(storage_path('app/public/'.$newPhotoPath));
    }

    public function test_oversized_photo_is_rejected_by_validation(): void
    {
        $user = User::factory()->create();
        $oversizedPhoto = UploadedFile::fake()->image('too-big.jpg', 5000, 5000);

        $response = $this->actingAs($user)->post(route('resumes.store'), [
            'name' => '解像度超過テスト',
            'photo' => $oversizedPhoto,
        ]);

        $response->assertSessionHasErrors(['photo']);
        $this->assertDatabaseCount('resumes', 0);
    }
}

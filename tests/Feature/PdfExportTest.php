<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\Resume;
use App\Models\User;
use App\Services\ExternalPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_request_resume_pdf_and_receives_pdf_response(): void
    {
        $user = User::factory()->create();
        $resume = Resume::create([
            'user_id' => $user->id,
            'name' => 'テスト太郎',
        ]);

        config(['services.pdf.enabled' => true]);

        $mockExternalPdf = Mockery::mock(ExternalPdfService::class);
        $mockExternalPdf->shouldReceive('generateFromHtml')
            ->once()
            ->withArgs(fn (string $html, array $options): bool => str_contains($html, 'テスト太郎')
                && ($options['filename'] ?? null) === 'resume-'.$resume->id.'.pdf')
            ->andReturn('%PDF-1.4 mock-resume-pdf');
        $this->app->instance(ExternalPdfService::class, $mockExternalPdf);

        $response = $this->actingAs($user)->get(route('resumes.pdf', $resume));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame('%PDF-1.4 mock-resume-pdf', $response->getContent());
    }

    public function test_owner_can_request_cv_pdf_and_receives_pdf_response(): void
    {
        $user = User::factory()->create();
        $cv = Cv::create([
            'user_id' => $user->id,
            'name' => '職務経歴テスト',
            'desired_position' => 'バックエンドエンジニア',
        ]);

        config(['services.pdf.enabled' => true]);

        $mockExternalPdf = Mockery::mock(ExternalPdfService::class);
        $mockExternalPdf->shouldReceive('generateFromHtml')
            ->once()
            ->withArgs(fn (string $html, array $options): bool => str_contains($html, '職務経歴テスト')
                && ($options['filename'] ?? null) === 'cv-'.$cv->id.'.pdf')
            ->andReturn('%PDF-1.4 mock-cv-pdf');
        $this->app->instance(ExternalPdfService::class, $mockExternalPdf);

        $response = $this->actingAs($user)->get(route('cvs.pdf', $cv));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame('%PDF-1.4 mock-cv-pdf', $response->getContent());
    }

    public function test_non_owner_cannot_access_resume_or_cv_pdf(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $resume = Resume::create([
            'user_id' => $owner->id,
            'name' => '非公開履歴書',
        ]);
        $cv = Cv::create([
            'user_id' => $owner->id,
            'name' => '非公開職務経歴書',
        ]);

        $this->actingAs($other)
            ->get(route('resumes.pdf', $resume))
            ->assertStatus(403);

        $this->actingAs($other)
            ->get(route('cvs.pdf', $cv))
            ->assertStatus(403);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * External PDF generation service adapter
 * Supports html2pdf.app (and can be extended for other services)
 */
class ExternalPdfService
{
    /**
     * Generate PDF from HTML using external service
     *
     * @return string|null PDF binary content or null on failure
     */
    public function generateFromHtml(string $html, array $options = []): ?string
    {
        $service = config('services.pdf.provider', 'html2pdf');

        try {
            switch ($service) {
                case 'html2pdf':
                    return $this->generateWithHtml2Pdf($html, $options);
                case 'pdfshift':
                    return $this->generateWithPdfShift($html, $options);
                default:
                    Log::error('Unknown PDF service provider: '.$service);

                    return null;
            }
        } catch (\Throwable $e) {
            Log::error('External PDF generation failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Generate PDF using html2pdf.app
     * Free tier: 100 requests/day
     */
    protected function generateWithHtml2Pdf(string $html, array $options = []): ?string
    {
        $apiKey = config('services.pdf.html2pdf_key');

        // If no API key, use the public endpoint (limited to 100/day)
        if (empty($apiKey)) {
            $url = 'https://html2pdf.app/api/generate';
        } else {
            $url = 'https://html2pdf.app/api/v1/generate';
        }

        $payload = [
            'html' => $html,
            'filename' => $options['filename'] ?? 'document.pdf',
            'format' => $options['format'] ?? 'A4',
            'orientation' => $options['orientation'] ?? 'portrait',
            'margin' => $options['margin'] ?? [
                'top' => '10mm',
                'right' => '10mm',
                'bottom' => '10mm',
                'left' => '10mm',
            ],
            'zoom' => $options['zoom'] ?? 0.75,
        ];

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (! empty($apiKey)) {
            $headers['Authorization'] = 'Bearer '.$apiKey;
        }

        $response = Http::timeout(60)
            ->withHeaders($headers)
            ->post($url, $payload);

        if ($response->successful()) {
            $body = $response->body();

            // Check if response is PDF (starts with %PDF)
            if (strpos($body, '%PDF') === 0) {
                return $body;
            }

            // Response might be JSON with PDF URL or base64
            $json = json_decode($body, true);
            if ($json && isset($json['pdf']) && is_string($json['pdf'])) {
                // If it's a trusted HTTPS URL on html2pdf.app, download it (mitigates SSRF)
                if (filter_var($json['pdf'], FILTER_VALIDATE_URL) && $this->isTrustedPdfUrl($json['pdf'], 'html2pdf.app')) {
                    $pdfResponse = Http::timeout(30)->get($json['pdf']);
                    if ($pdfResponse->successful()) {
                        return $pdfResponse->body();
                    }
                }
                // If it's base64, decode it
                $decoded = base64_decode($json['pdf'], true);
                if ($decoded !== false && strpos($decoded, '%PDF') === 0) {
                    return $decoded;
                }
            }

            Log::error('html2pdf.app returned unexpected format');

            return null;
        }

        Log::error('html2pdf.app API failed: '.$response->status().' - '.$response->body());

        return null;
    }

    /**
     * Generate PDF using PDFShift
     * Free tier: 500 conversions/month
     */
    protected function generateWithPdfShift(string $html, array $options = []): ?string
    {
        $apiKey = config('services.pdf.pdfshift_key');

        if (empty($apiKey)) {
            Log::error('PDFShift API key not configured');

            return null;
        }

        $payload = [
            'source' => $html,
            'landscape' => ($options['orientation'] ?? 'portrait') === 'landscape',
            'format' => $options['format'] ?? 'A4',
            'margin' => $options['margin'] ?? '10mm',
            'zoom' => $options['zoom'] ?? 0.75,
        ];

        $response = Http::timeout(60)
            ->withBasicAuth($apiKey, '')
            ->post('https://api.pdfshift.io/v3/convert/pdf', $payload);

        if ($response->successful()) {
            return $response->body();
        }

        Log::error('PDFShift API failed: '.$response->status().' - '.$response->body());

        return null;
    }

    /**
     * Validate that a returned download URL uses HTTPS and belongs to the expected provider domain.
     */
    protected function isTrustedPdfUrl(string $url, string $allowedDomain): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');

        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        return $host === $allowedDomain || str_ends_with($host, '.'.$allowedDomain);
    }
}

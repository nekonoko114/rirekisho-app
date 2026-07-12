<?php

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Converts rendered HTML into a PDF download response.
 *
 * Generation is attempted in priority order and falls through on failure:
 *  1. External API (ExternalPdfService) when services.pdf.enabled is true
 *     — the only option on shared hosting without wkhtmltopdf.
 *  2. Snappy (barryvdh/laravel-snappy) when the binding is available.
 *  3. Direct wkhtmltopdf shell-out via PdfGenerator.
 * If all fail, the raw HTML is returned with an X-PDF-Error header.
 */
class PdfExportService
{
    public function respond(string $html, string $filename, float $zoom = 0.75, string $orientation = 'Landscape'): Response
    {
        $lowerOrientation = strtolower($orientation);

        if (config('services.pdf.enabled', false)) {
            try {
                $pdfContent = app(ExternalPdfService::class)->generateFromHtml($html, [
                    'filename' => $filename,
                    'zoom' => $zoom,
                    'orientation' => $lowerOrientation,
                ]);

                if ($pdfContent) {
                    return $this->pdfResponse($pdfContent, $filename, 'inline');
                }
            } catch (\Throwable $e) {
                Log::error("External PDF generation failed for {$filename}: ".$e->getMessage());
            }
        }

        if ($this->snappyAvailable()) {
            try {
                $pdf = app('snappy.pdf.wrapper')->loadHTML($html);
                try {
                    $pdf->setOption('zoom', $zoom);
                    $pdf->setOption('orientation', $orientation);
                } catch (\Throwable $e) {
                    Log::warning('Unable to set snappy zoom/orientation option: '.$e->getMessage());
                }

                return $this->pdfResponse($pdf->output(), $filename, 'inline');
            } catch (\Throwable $e) {
                Log::error("PDF generation (snappy) failed for {$filename}: ".$e->getMessage());
            }
        }

        try {
            $cfg = config('snappy.pdf.options', []);
            $cfg['zoom'] = $zoom;
            $cfg['orientation'] = $orientation;
            config(['snappy.pdf.options' => $cfg]);

            $pdfContent = (new PdfGenerator)->outputFromHtml($html);

            return $this->pdfResponse($pdfContent, $filename, 'inline');
        } catch (\Throwable $e) {
            Log::error("PDF generation fallback failed for {$filename}: ".$e->getMessage());

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-PDF-Error' => 'true',
            ]);
        }
    }

    protected function snappyAvailable(): bool
    {
        try {
            return app()->bound('snappy.pdf') && class_exists('\Knp\Snappy\Pdf');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function pdfResponse(string $content, string $filename, string $disposition): Response
    {
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ]);
    }
}

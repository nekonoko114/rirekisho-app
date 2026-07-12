<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PdfGenerator
{
    public function outputFromHtml(string $html): string
    {
        $binary = config('snappy.pdf.binary', '/usr/bin/wkhtmltopdf');
        $options = config('snappy.pdf.options', []);
        $env = config('snappy.pdf.env', []);

        $tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel_snappy';
        if (! is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }

        $inFile = tempnam($tmpDir, 'html_').'.html';
        $outFile = tempnam($tmpDir, 'pdf_').'.pdf';

        file_put_contents($inFile, $html);

        // Build args
        $args = [$binary];
        foreach ($options as $k => $v) {
            $flag = '--'.$k;
            if (is_bool($v)) {
                if ($v) {
                    $args[] = $flag;
                }
            } else {
                $args[] = $flag;
                $args[] = (string) $v;
            }
        }

        // Input and output
        $args[] = $inFile;
        $args[] = $outFile;

        try {
            $process = new Process($args, null, $env, null, null);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::error('wkhtmltopdf failed: '.$process->getErrorOutput());
                throw new \RuntimeException('wkhtmltopdf failed: '.$process->getErrorOutput());
            }

            $contents = @file_get_contents($outFile);

            // cleanup
            @unlink($inFile);
            @unlink($outFile);

            if ($contents === false) {
                throw new \RuntimeException('Failed to read generated PDF');
            }

            return $contents;
        } catch (\Throwable $e) {
            @unlink($inFile);
            @unlink($outFile);
            Log::error('PdfGenerator error: '.$e->getMessage());
            throw $e;
        }
    }
}

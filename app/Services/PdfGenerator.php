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
            @mkdir($tmpDir, 0700, true);
        }

        $inBase = tempnam($tmpDir, 'html_');
        $outBase = tempnam($tmpDir, 'pdf_');
        $inFile = $inBase.'.html';
        $outFile = $outBase.'.pdf';

        file_put_contents($inFile, $html);
        @chmod($inFile, 0600);

        if (! array_key_exists('disable-javascript', $options)) {
            $options['disable-javascript'] = true;
        }

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
            $process = new Process($args, null, $env, null, 30);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::error('wkhtmltopdf failed: '.$process->getErrorOutput());
                throw new \RuntimeException('wkhtmltopdf failed: '.$process->getErrorOutput());
            }

            $contents = @file_get_contents($outFile);

            if ($contents === false) {
                throw new \RuntimeException('Failed to read generated PDF');
            }

            return $contents;
        } catch (\Throwable $e) {
            Log::error('PdfGenerator error: '.$e->getMessage());
            throw $e;
        } finally {
            @unlink((string) $inBase);
            @unlink((string) $outBase);
            @unlink($inFile);
            @unlink($outFile);
        }
    }
}

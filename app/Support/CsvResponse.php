<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a CSV download with a UTF-8 BOM for Excel compatibility.
 */
class CsvResponse
{
    /**
     * @param  array  $header  column labels for the first row
     * @param  iterable<array>  $rows  data rows
     */
    public static function stream(string $filename, array $header, iterable $rows): StreamedResponse
    {
        $safeFilename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($filename)) ?: 'export.csv';

        return response()->stream(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, array_map([self::class, 'sanitizeCell'], $header));
            foreach ($rows as $row) {
                fputcsv($out, array_map([self::class, 'sanitizeCell'], (array) $row));
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$safeFilename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Neutralize spreadsheet formula injection payloads (=, +, -, @, tab, CR/LF).
     */
    public static function sanitizeCell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (preg_match('/^[\s]*[=+\-@\t\r\n]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}

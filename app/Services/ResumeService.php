<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\ResumeProfile;
use App\Services\Concerns\SyncsChildRecords;
use Illuminate\Http\Request;

class ResumeService
{
    use SyncsChildRecords;

    /**
     * Create histories, licenses and profile from request for a given resume.
     */
    public function createFromRequest(Resume $resume, Request $request): void
    {
        $this->createChildren($resume->histories(), $this->historiesInput($request), $this->mapHistoryRow(...));

        $licenses = (array) $request->input('licenses', []);
        if (! empty($licenses)) {
            $this->createChildren($resume->licenses(), $licenses, $this->mapLicenseRow(...));
        } else {
            $this->createLicensesFromText($resume, (string) $request->input('licenses_text', ''));
        }

        ResumeProfile::create([
            'resume_id' => $resume->id,
            'motivation' => $request->input('motivation'),
            'personal_requests' => $request->input('personal_requests'),
        ]);
    }

    /**
     * Update related models using differential sync (insert/update/delete).
     */
    public function updateFromRequest(Resume $resume, Request $request): void
    {
        $this->syncChildren($resume->histories(), $this->historiesInput($request), $this->mapHistoryRow(...));

        $licenses = (array) $request->input('licenses', []);
        if (! empty($licenses)) {
            $this->syncChildren($resume->licenses(), $licenses, $this->mapLicenseRow(...));
        } else {
            // Text fallback replaces the whole license list (empty text clears it)
            $resume->licenses()->delete();
            $this->createLicensesFromText($resume, (string) $request->input('licenses_text', ''));
        }

        $resume->profile()->updateOrCreate([], [
            'motivation' => $request->input('motivation'),
            'personal_requests' => $request->input('personal_requests'),
        ]);
    }

    /**
     * Education and work histories are submitted as separate arrays; merge
     * them in order so sort_order reflects the combined sequence.
     */
    protected function historiesInput(Request $request): array
    {
        return array_merge(
            (array) $request->input('histories', []),
            (array) $request->input('histories_education', []),
            (array) $request->input('histories_work', [])
        );
    }

    protected function mapHistoryRow(array $h, int $i): ?array
    {
        if (empty($h['description']) && empty($h['year']) && empty($h['month'])) {
            return null;
        }

        return [
            'year' => $h['year'] ?? null,
            'month' => $h['month'] ?? null,
            'type' => $h['type'] ?? 'education',
            'description' => $h['description'] ?? null,
            'sort_order' => $i,
        ];
    }

    protected function mapLicenseRow(array $l, int $i): ?array
    {
        if (empty($l['name'])) {
            return null;
        }

        return [
            'year' => $l['year'] ?? null,
            'month' => $l['month'] ?? null,
            'name' => $l['name'],
            'details' => $l['details'] ?? null,
        ];
    }

    /**
     * Legacy fallback: parse a free-text block, one license per line,
     * optionally prefixed with "YYYY MM".
     */
    protected function createLicensesFromText(Resume $resume, string $text): void
    {
        if (trim($text) === '') {
            return;
        }

        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^(\d{4})[^0-9]*(\d{1,2})?\s*(.*)$/u', $line, $m)) {
                $year = $m[1];
                $month = ! empty($m[2]) ? $m[2] : null;
                $rest = trim($m[3]);
            } else {
                $year = null;
                $month = null;
                $rest = $line;
            }

            $resume->licenses()->create([
                'year' => $year,
                'month' => $month,
                'name' => $rest,
                'details' => null,
            ]);
        }
    }
}

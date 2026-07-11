<?php

namespace App\Services;

use App\Models\Cv;
use App\Services\Concerns\SyncsChildRecords;
use Illuminate\Http\Request;

class CvService
{
    use SyncsChildRecords;

    public function createFromRequest(Cv $cv, Request $request): void
    {
        $this->createChildren($cv->histories(), (array) $request->input('histories', []), $this->mapHistoryRow(...));
        $this->createChildren($cv->licenses(), (array) $request->input('licenses', []), $this->mapLicenseRow(...));
    }

    public function updateFromRequest(Cv $cv, Request $request): void
    {
        $this->syncChildren($cv->histories(), (array) $request->input('histories', []), $this->mapHistoryRow(...));
        $this->syncChildren($cv->licenses(), (array) $request->input('licenses', []), $this->mapLicenseRow(...));
    }

    protected function mapHistoryRow(array $h, int $i): ?array
    {
        if (empty($h['company_name']) && empty($h['job_description'])) {
            return null;
        }

        return [
            'company_name' => $h['company_name'] ?? null,
            'start_year' => $h['start_year'] ?? null,
            'start_month' => $h['start_month'] ?? null,
            'end_year' => $h['end_year'] ?? null,
            'end_month' => $h['end_month'] ?? null,
            'job_description' => $h['job_description'] ?? null,
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
        ];
    }
}

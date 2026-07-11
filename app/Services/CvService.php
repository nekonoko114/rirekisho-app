<?php

namespace App\Services;

use App\Models\Cv;
use App\Models\CvHistory;
use App\Models\CvLicense;
use Illuminate\Http\Request;

class CvService
{
    public function createFromRequest(Cv $cv, Request $request): void
    {
        $this->createOrUpdateHistories($cv, $request);
        $this->createOrUpdateLicenses($cv, $request);
    }

    public function updateFromRequest(Cv $cv, Request $request): void
    {
        $this->syncHistories($cv, $request);
        $this->syncLicenses($cv, $request);
    }

    protected function syncHistories(Cv $cv, Request $request): void
    {
        $existing = $cv->histories()->get()->keyBy('id');
        $historiesInput = $request->input('histories', []);
        $seenIds = [];

        foreach ($historiesInput as $i => $h) {
            if (empty($h['company_name']) && empty($h['job_description'])) {
                continue;
            }

            $data = [
                'cv_id' => $cv->id,
                'company_name' => $h['company_name'] ?? null,
                'start_year' => $h['start_year'] ?? null,
                'start_month' => $h['start_month'] ?? null,
                'end_year' => $h['end_year'] ?? null,
                'end_month' => $h['end_month'] ?? null,
                'job_description' => $h['job_description'] ?? null,
            ];

            if (! empty($h['id']) && isset($existing[$h['id']])) {
                $existing[$h['id']]->update($data);
                $seenIds[] = $h['id'];
            } else {
                CvHistory::create($data);
            }
        }

        $toDelete = $existing->keys()->diff($seenIds);
        if ($toDelete->isNotEmpty()) {
            CvHistory::whereIn('id', $toDelete->toArray())->delete();
        }
    }

    protected function syncLicenses(Cv $cv, Request $request): void
    {
        $existing = $cv->licenses()->get()->keyBy('id');
        $licensesInput = $request->input('licenses', []);
        $seenIds = [];

        foreach ($licensesInput as $i => $l) {
            if (empty($l['name'])) {
                continue;
            }

            $data = [
                'cv_id' => $cv->id,
                'year' => $l['year'] ?? null,
                'month' => $l['month'] ?? null,
                'name' => $l['name'] ?? null,
            ];

            if (! empty($l['id']) && isset($existing[$l['id']])) {
                $existing[$l['id']]->update($data);
                $seenIds[] = $l['id'];
            } else {
                CvLicense::create($data);
            }
        }

        $toDelete = $existing->keys()->diff($seenIds);
        if ($toDelete->isNotEmpty()) {
            CvLicense::whereIn('id', $toDelete->toArray())->delete();
        }
    }

    protected function createOrUpdateHistories(Cv $cv, Request $request): void
    {
        $historiesInput = $request->input('histories', []);
        foreach ($historiesInput as $i => $h) {
            if (empty($h['company_name']) && empty($h['job_description'])) {
                continue;
            }
            CvHistory::create([
                'cv_id' => $cv->id,
                'company_name' => $h['company_name'] ?? null,
                'start_year' => $h['start_year'] ?? null,
                'start_month' => $h['start_month'] ?? null,
                'end_year' => $h['end_year'] ?? null,
                'end_month' => $h['end_month'] ?? null,
                'job_description' => $h['job_description'] ?? null,
            ]);
        }
    }

    protected function createOrUpdateLicenses(Cv $cv, Request $request): void
    {
        $licensesInput = $request->input('licenses', []);
        foreach ($licensesInput as $i => $l) {
            if (empty($l['name'])) {
                continue;
            }
            CvLicense::create([
                'cv_id' => $cv->id,
                'year' => $l['year'] ?? null,
                'month' => $l['month'] ?? null,
                'name' => $l['name'] ?? null,
            ]);
        }
    }
}

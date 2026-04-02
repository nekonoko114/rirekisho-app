<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\ResumeHistory;
use App\Models\ResumeLicense;
use App\Models\ResumeProfile;
use Illuminate\Http\Request;

class ResumeService
{
    /**
     * Create histories, licenses and profile from request for a given resume.
     */
    public function createFromRequest(Resume $resume, Request $request): void
    {
        $this->createOrUpdateHistories($resume, $request);
        $this->createOrUpdateLicenses($resume, $request);
        $this->createOrUpdateProfile($resume, $request);
    }

    /**
     * Update related models using differential sync (insert/update/delete).
     */
    public function updateFromRequest(Resume $resume, Request $request): void
    {
        $this->syncHistories($resume, $request);
        $this->syncLicenses($resume, $request);
        $this->syncProfile($resume, $request);
    }

    protected function syncHistories(Resume $resume, Request $request): void
    {
        $existing = $resume->histories()->get()->keyBy('id');

        $historiesInput = [];
        $historiesInput = array_merge(
            $historiesInput,
            (array) $request->input('histories', []),
            (array) $request->input('histories_education', []),
            (array) $request->input('histories_work', [])
        );

        $seenIds = [];
        foreach ($historiesInput as $i => $h) {
            if (empty($h['description']) && empty($h['year']) && empty($h['month'])) {
                continue;
            }

            $data = [
                'resume_id' => $resume->id,
                'year' => $h['year'] ?? null,
                'month' => $h['month'] ?? null,
                'type' => $h['type'] ?? 'education',
                'description' => $h['description'] ?? null,
                'sort_order' => $i,
            ];

            if (! empty($h['id']) && isset($existing[$h['id']])) {
                $existing[$h['id']]->update($data);
                $seenIds[] = $h['id'];
            } else {
                ResumeHistory::create($data);
            }
        }

        // delete any existing records not present in incoming payload
        $toDelete = $existing->keys()->diff($seenIds);
        if ($toDelete->isNotEmpty()) {
            ResumeHistory::whereIn('id', $toDelete->toArray())->delete();
        }
    }

    protected function syncLicenses(Resume $resume, Request $request): void
    {
        $existing = $resume->licenses()->get()->keyBy('id');

        $licensesInput = $request->input('licenses', []);
        $seenIds = [];

        if (! empty($licensesInput)) {
            foreach ($licensesInput as $i => $l) {
                if (empty($l['name'])) {
                    continue;
                }

                $data = [
                    'resume_id' => $resume->id,
                    'year' => $l['year'] ?? null,
                    'month' => $l['month'] ?? null,
                    'name' => $l['name'] ?? null,
                    'details' => $l['details'] ?? null,
                ];

                if (! empty($l['id']) && isset($existing[$l['id']])) {
                    $existing[$l['id']]->update($data);
                    $seenIds[] = $l['id'];
                } else {
                    ResumeLicense::create($data);
                }
            }
        } else {
            // fallback to text parsing when licenses array not provided
            $licensesText = $request->input('licenses_text', '');
            if (! empty(trim($licensesText))) {
                $lines = preg_split('/\r\n|\r|\n/', $licensesText);
                foreach ($lines as $i => $line) {
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
                    ResumeLicense::create([
                        'resume_id' => $resume->id,
                        'year' => $year,
                        'month' => $month,
                        'name' => $rest,
                        'details' => null,
                    ]);
                }
                // when using text fallback, remove all previous licenses
                $existing->keys()->each(function ($id) use ($resume) {
                    ResumeLicense::where('id', $id)->delete();
                });
                return;
            }
        }

        // delete any existing licenses not present in incoming payload
        $toDelete = $existing->keys()->diff($seenIds);
        if ($toDelete->isNotEmpty()) {
            ResumeLicense::whereIn('id', $toDelete->toArray())->delete();
        }
    }

    protected function syncProfile(Resume $resume, Request $request): void
    {
        $motivation = $request->input('motivation', null);
        $personal = $request->input('personal_requests', null);

        $profile = $resume->profile;
        if ($profile) {
            $profile->update([
                'motivation' => $motivation,
                'personal_requests' => $personal,
            ]);
        } else {
            ResumeProfile::create([
                'resume_id' => $resume->id,
                'motivation' => $motivation,
                'personal_requests' => $personal,
            ]);
        }
    }

    protected function createOrUpdateHistories(Resume $resume, Request $request): void
    {
        $historiesInput = [];
        $historiesInput = array_merge(
            $historiesInput,
            (array) $request->input('histories', []),
            (array) $request->input('histories_education', []),
            (array) $request->input('histories_work', [])
        );

        if (! empty($historiesInput)) {
            foreach ($historiesInput as $i => $h) {
                if (empty($h['description']) && empty($h['year']) && empty($h['month'])) {
                    continue;
                }
                ResumeHistory::create([
                    'resume_id' => $resume->id,
                    'year' => $h['year'] ?? null,
                    'month' => $h['month'] ?? null,
                    'type' => $h['type'] ?? 'education',
                    'description' => $h['description'] ?? null,
                    'sort_order' => $i,
                ]);
            }
        }
    }

    protected function createOrUpdateLicenses(Resume $resume, Request $request): void
    {
        $licensesInput = $request->input('licenses', []);
        if (! empty($licensesInput)) {
            foreach ($licensesInput as $i => $l) {
                if (empty($l['name'])) {
                    continue;
                }
                ResumeLicense::create([
                    'resume_id' => $resume->id,
                    'year' => $l['year'] ?? null,
                    'month' => $l['month'] ?? null,
                    'name' => $l['name'] ?? null,
                    'details' => $l['details'] ?? null,
                ]);
            }
            return;
        }

        $licensesText = $request->input('licenses_text', '');
        if (! empty(trim($licensesText))) {
            $lines = preg_split('/\r\n|\r|\n/', $licensesText);
            foreach ($lines as $i => $line) {
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
                ResumeLicense::create([
                    'resume_id' => $resume->id,
                    'year' => $year,
                    'month' => $month,
                    'name' => $rest,
                    'details' => null,
                ]);
            }
        }
    }

    protected function createOrUpdateProfile(Resume $resume, Request $request): void
    {
        $motivation = $request->input('motivation', null);
        $personal = $request->input('personal_requests', null);

        ResumeProfile::create([
            'resume_id' => $resume->id,
            'motivation' => $motivation,
            'personal_requests' => $personal,
        ]);
    }
}

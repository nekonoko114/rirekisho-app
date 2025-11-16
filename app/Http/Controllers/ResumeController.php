<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResumeStoreRequest;
use App\Http\Requests\ResumeUpdateRequest;
use App\Models\Resume;
use App\Models\ResumeHistory;
use App\Models\ResumeLicense;
use App\Models\ResumeProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    public function create()
    {
        return view('resume.create');
    }

    public function index()
    {
        $user = Auth::user();

        $query = Resume::withCount(['histories', 'licenses'])->orderBy('id', 'desc');

        // If user is not admin, limit to their own resumes
        $isAdminUser = ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
        if (! $isAdminUser) {
            $query->where('user_id', $user->id);
        }

        $resumes = $query->paginate(15);

        return view('resume.index', compact('resumes'));
    }

    public function store(ResumeStoreRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $validated['photo_path'] = $path;
        }

        // Attach user_id if authenticated, otherwise generate a public token
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        } else {
            $validated['public_token'] = bin2hex(\random_bytes(16));
        }

        $resume = Resume::create($validated);

        // histories: accept multiple input shapes. Merge any available arrays (legacy 'histories',
        // or split sections 'histories_education' and 'histories_work') so the controller is robust
        // against frontend naming differences.
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

        // licenses: array or simple textarea
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
        } else {
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

        // profile
        ResumeProfile::create([
            'resume_id' => $resume->id,
            'motivation' => $validated['motivation'] ?? null,
            'personal_requests' => $validated['personal_requests'] ?? null,
        ]);

        // If guest created it, provide a public link containing the token
        if (! $request->user()) {
            // Redirect guest directly to the public page with token
            return redirect()->route('resumes.show', ['resume' => $resume->id, 'token' => $resume->public_token])->with('status', '履歴書を保存しました');
        }

        return redirect()->route('resumes.show', $resume)->with('status', '履歴書を保存しました');
    }

    public function show(Resume $resume)
    {
        $resume->load(['histories', 'licenses', 'profile']);

        $user = Auth::user();
        $token = request()->query('token');

        // Allow if:
        // - logged-in owner
        // - logged-in admin
        // - or public token matches for guest-created resumes
        if ($user) {
            // Allow if owner or admin. Note: allow admin even if resume->user_id is null (guest-created resumes).
            $isOwner = ($resume->user_id && $resume->user_id === $user->id);
            $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin());
            if ($isOwner || $isAdmin) {
                return view('resume.show', compact('resume'));
            }
            // logged-in but not owner/admin -> forbidden
            abort(403, 'この履歴書を表示する権限がありません');
        }

        // If resume has public_token and token matches, allow (guest access)
        if ($resume->public_token && $token && \hash_equals($resume->public_token, $token)) {
            return view('resume.show', compact('resume'));
        }

        // Not authorized: redirect guests to login
        return redirect()->route('login');
    }

    /**
     * Generate PDF for a resume using snappy (if installed).
     */
    public function pdf(Resume $resume)
    {
        // Reuse the same authorization logic as show
        $user = Auth::user();
        $token = request()->query('token');

        if ($user) {
            // Allow owner or admin (admin allowed even for guest-created resumes)
            $isOwner = ($resume->user_id && $resume->user_id === $user->id);
            $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin());
            if (! ($isOwner || $isAdmin)) {
                abort(403, 'この履歴書を表示する権限がありません');
            }
        } else {
            if (! ($resume->public_token && $token && \hash_equals($resume->public_token, $token))) {
                return redirect()->route('login');
            }
        }

        // Render the same view but instruct it we're rendering for PDF (disable print button)
        $html = view('resume.show', ['resume' => $resume, 'forPdf' => true])->render();

        // Prefer Snappy if fully available (bindings present and Knp class exists)
        try {
            $useSnappy = app()->bound('snappy.pdf') && class_exists('\Knp\\Snappy\\Pdf');
        } catch (\Throwable $e) {
            $useSnappy = false;
        }

        if ($useSnappy) {
            try {
                $pdf = app('snappy.pdf.wrapper')->loadHTML($html);
                $filename = 'resume-'.$resume->id.'.pdf';

                return response($pdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$filename.'"',
                ]);
            } catch (\Throwable $e) {
                Log::error('PDF generation (snappy) failed for resume '.$resume->id.': '.$e->getMessage());
                // fall through to fallback generator
            }
        }

        // Fallback: use local PdfGenerator (direct wkhtmltopdf call)
        try {
            $generator = new \App\Services\PdfGenerator;
            $pdfContent = $generator->outputFromHtml($html);
            $filename = 'resume-'.$resume->id.'.pdf';

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        } catch (\Throwable $e) {
            Log::error('PDF generation fallback failed for resume '.$resume->id.': '.$e->getMessage());

            // Final fallback: return HTML so the user can still view/print from the browser.
            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-PDF-Error' => 'true',
            ]);
        }
    }

    public function edit(Resume $resume)
    {
        $user = Auth::user();
        // allow owner or admin
        if (! ($user && (($resume->user_id && $resume->user_id === $user->id) || (method_exists($user, 'isAdmin') && $user->isAdmin())))) {
            abort(403, 'この履歴書を編集する権限がありません');
        }

        $resume->load(['histories', 'licenses', 'profile']);

        return view('resume.edit', compact('resume'));
    }

    public function update(ResumeUpdateRequest $request, Resume $resume)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            // remove old photo if present
            if ($resume->photo_path) {
                Storage::disk('public')->delete($resume->photo_path);
            }
            $path = $request->file('photo')->store('photos', 'public');
            $validated['photo_path'] = $path;
        }

        $resume->update($validated);

        // replace histories/licenses/profile: delete existing and recreate from request
        $resume->histories()->delete();
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

        $resume->licenses()->delete();
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
        }

        // profile: update or create
        $profile = $resume->profile;
        if ($profile) {
            $profile->update([
                'motivation' => $validated['motivation'] ?? null,
                'personal_requests' => $validated['personal_requests'] ?? null,
            ]);
        } else {
            ResumeProfile::create([
                'resume_id' => $resume->id,
                'motivation' => $validated['motivation'] ?? null,
                'personal_requests' => $validated['personal_requests'] ?? null,
            ]);
        }

        return redirect()->route('resumes.show', $resume)->with('status', '履歴書を更新しました');
    }

    /**
     * Revoke public token for a resume (owner or admin only)
     */
    public function revokePublic(Resume $resume)
    {
        $user = Auth::user();
        $isOwner = ($user && $resume->user_id && $resume->user_id === $user->id);
        $isAdmin = ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
        if (! ($isOwner || $isAdmin)) {
            abort(403, 'この操作を行う権限がありません');
        }

        $resume->public_token = null;
        $resume->save();

        return redirect()->back()->with('status', '公開リンクを無効化しました');
    }

    public function destroy(Resume $resume)
    {
        $user = Auth::user();
        $isOwner = ($user && $resume->user_id && $resume->user_id === $user->id);
        $isAdmin = ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
        if (! ($isOwner || $isAdmin)) {
            abort(403, 'この履歴書を削除する権限がありません');
        }

        // delete related resources
        if ($resume->photo_path) {
            Storage::disk('public')->delete($resume->photo_path);
        }
        $resume->histories()->delete();
        $resume->licenses()->delete();
        $resume->profile()->delete();
        $resume->delete();

        return redirect()->route('resumes.index')->with('status', '履歴書を削除しました');
    }
}

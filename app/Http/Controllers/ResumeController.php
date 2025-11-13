<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeHistory;
use App\Models\ResumeLicense;
use App\Models\ResumeProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class ResumeController extends Controller
{
    public function create()
    {
        return view('resume.create');
    }

    public function index()
    {
        $user = Auth::user();

        $query = Resume::withCount(['histories','licenses'])->orderBy('id','desc');

        // If user is not admin, limit to their own resumes
        $isAdminUser = ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
        if (! $isAdminUser) {
            $query->where('user_id', $user->id);
        }

        $resumes = $query->paginate(15);
        return view('resume.index', compact('resumes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'furigana' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'address_postal' => ['nullable','string','max:20','regex:/^\d{3}-\d{4}$/'],
            'contact_address' => 'nullable|string',
            'contact_postal' => ['nullable','string','max:20','regex:/^\d{3}-\d{4}$/'],
            'contact_phone' => 'nullable|string|max:50',
            'photo' => 'nullable|image|max:2048',
            'histories' => 'nullable|array',
            'histories_education_text' => 'nullable|string',
            'histories_work_text' => 'nullable|string',
            'licenses' => 'nullable|array',
            'licenses_text' => 'nullable|string',
            'motivation' => 'nullable|string',
            'personal_requests' => 'nullable|string',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $validated['photo_path'] = $path;
        }

        // Fallback combination when client-side JS is disabled: combine parts into main fields
        // Phone
        if (empty($validated['phone'])) {
            $p1 = $request->input('phone_part1', '');
            $p2 = $request->input('phone_part2', '');
            $p3 = $request->input('phone_part3', '');
            $parts = array_filter([$p1, $p2, $p3], function ($v) { return trim((string)$v) !== ''; });
            if (!empty($parts)) {
                $validated['phone'] = implode('-', $parts);
            }
        }
        if (empty($validated['contact_phone'])) {
            $cp1 = $request->input('contact_phone_part1', '');
            $cp2 = $request->input('contact_phone_part2', '');
            $cp3 = $request->input('contact_phone_part3', '');
            $cparts = array_filter([$cp1, $cp2, $cp3], function ($v) { return trim((string)$v) !== ''; });
            if (!empty($cparts)) {
                $validated['contact_phone'] = implode('-', $cparts);
            }
        }
        // Postal
        if (empty($validated['address_postal'])) {
            $ap1 = $request->input('address_postal_part1', '');
            $ap2 = $request->input('address_postal_part2', '');
            $aparts = array_filter([$ap1, $ap2], function ($v) { return trim((string)$v) !== ''; });
            if (!empty($aparts)) {
                $validated['address_postal'] = implode('-', $aparts);
            }
        }
        if (empty($validated['contact_postal'])) {
            $cap1 = $request->input('contact_postal_part1', '');
            $cap2 = $request->input('contact_postal_part2', '');
            $caparts = array_filter([$cap1, $cap2], function ($v) { return trim((string)$v) !== ''; });
            if (!empty($caparts)) {
                $validated['contact_postal'] = implode('-', $caparts);
            }
        }

        // Post-combination validation: ensure phone fields contain only digits and hyphens
        if (!empty($validated['phone']) && !preg_match('/^[0-9-]+$/', $validated['phone'])) {
            return back()->withInput()->withErrors(['phone' => '電話番号は数字とハイフンのみ使用できます']);
        }
        if (!empty($validated['contact_phone']) && !preg_match('/^[0-9-]+$/', $validated['contact_phone'])) {
            return back()->withInput()->withErrors(['contact_phone' => '連絡先電話は数字とハイフンのみ使用できます']);
        }

        // Post-combination validation: ensure postal codes match NNN-NNNN if present
        if (!empty($validated['address_postal']) && !preg_match('/^\d{3}-\d{4}$/', $validated['address_postal'])) {
            return back()->withInput()->withErrors(['address_postal' => '郵便番号は「123-4567」の形式で入力してください']);
        }
        if (!empty($validated['contact_postal']) && !preg_match('/^\d{3}-\d{4}$/', $validated['contact_postal'])) {
            return back()->withInput()->withErrors(['contact_postal' => '連絡先の郵便番号は「123-4567」の形式で入力してください']);
        }

        // Attach user_id if authenticated, otherwise we'll generate a public token
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        } else {
            // generate a secure token for guest access
            $validated['public_token'] = bin2hex(\random_bytes(16));
        }

        $resume = Resume::create($validated);

        // histories: accept both array inputs and simplified textarea inputs
        $historiesInput = $request->input('histories', []);
        if (!empty($historiesInput)) {
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
        } else {
            // parse education lines
            $eduText = $request->input('histories_education_text', '');
            if (!empty(trim($eduText))) {
                $lines = preg_split('/\r\n|\r|\n/', $eduText);
                foreach ($lines as $i => $line) {
                    $line = trim($line);
                    if ($line === '') continue;
                    // try to extract year/month at start: formats like 2010/04 or 2010-04 or 2010年04月
                    if (preg_match('/^(\d{4})[^0-9]*(\d{1,2})?\s*(.*)$/u', $line, $m)) {
                        $year = $m[1];
                        $month = !empty($m[2]) ? $m[2] : null;
                        $desc = trim($m[3]);
                    } else {
                        $year = null; $month = null; $desc = $line;
                    }
                    ResumeHistory::create([
                        'resume_id' => $resume->id,
                        'year' => $year,
                        'month' => $month,
                        'type' => 'education',
                        'description' => $desc,
                        'sort_order' => $i,
                    ]);
                }
            }

            // parse work lines
            $workText = $request->input('histories_work_text', '');
            if (!empty(trim($workText))) {
                $lines = preg_split('/\r\n|\r|\n/', $workText);
                foreach ($lines as $i => $line) {
                    $line = trim($line);
                    if ($line === '') continue;
                    if (preg_match('/^(\d{4})[^0-9]*(\d{1,2})?\s*(.*)$/u', $line, $m)) {
                        $year = $m[1];
                        $month = !empty($m[2]) ? $m[2] : null;
                        $desc = trim($m[3]);
                    } else {
                        $year = null; $month = null; $desc = $line;
                    }
                    ResumeHistory::create([
                        'resume_id' => $resume->id,
                        'year' => $year,
                        'month' => $month,
                        'type' => 'work',
                        'description' => $desc,
                        'sort_order' => $i,
                    ]);
                }
            }
        }

        // licenses: array or simple textarea
        $licensesInput = $request->input('licenses', []);
        if (!empty($licensesInput)) {
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
            if (!empty(trim($licensesText))) {
                $lines = preg_split('/\r\n|\r|\n/', $licensesText);
                foreach ($lines as $i => $line) {
                    $line = trim($line);
                    if ($line === '') continue;
                    if (preg_match('/^(\d{4})[^0-9]*(\d{1,2})?\s*(.*)$/u', $line, $m)) {
                        $year = $m[1];
                        $month = !empty($m[2]) ? $m[2] : null;
                        $rest = trim($m[3]);
                    } else {
                        $year = null; $month = null; $rest = $line;
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
        $resume->load(['histories','licenses','profile']);

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

        if (! app()->bound('snappy.pdf')) {
            // Snappy not installed — inform user and offer HTML preview fallback
            return response($html);
        }

        // Use the wrapper binding which provides convenience methods like loadHTML()
        try {
            $pdf = app('snappy.pdf.wrapper')->loadHTML($html);
            $filename = 'resume-' . $resume->id . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            Log::error('PDF generation failed for resume ' . $resume->id . ': ' . $e->getMessage());

            // Fallback: return the HTML so the user can still view/print from the browser.
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

        $resume->load(['histories','licenses','profile']);
        return view('resume.edit', compact('resume'));
    }

    public function update(Request $request, Resume $resume)
    {
        // (removed debug logging)

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'furigana' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'address_postal' => ['nullable','string','max:20','regex:/^\d{3}-\d{4}$/'],
            'contact_address' => 'nullable|string',
            'contact_postal' => ['nullable','string','max:20','regex:/^\d{3}-\d{4}$/'],
            'contact_phone' => 'nullable|string|max:50',
            'photo' => 'nullable|image|max:2048',
            'histories' => 'nullable|array',
            'licenses' => 'nullable|array',
            'motivation' => 'nullable|string',
            'personal_requests' => 'nullable|string',
        ]);

        if ($request->hasFile('photo')) {
            // remove old photo if present
            if ($resume->photo_path) {
                Storage::disk('public')->delete($resume->photo_path);
            }
            $path = $request->file('photo')->store('photos', 'public');
            $validated['photo_path'] = $path;
        }

        // combine split fields same as store
        if (empty($validated['phone'])) {
            $p1 = $request->input('phone_part1', '');
            $p2 = $request->input('phone_part2', '');
            $p3 = $request->input('phone_part3', '');
            $parts = array_filter([$p1, $p2, $p3], function ($v) { return trim((string)$v) !== ''; });
            if (!empty($parts)) {
                $validated['phone'] = implode('-', $parts);
            }
        }
        if (empty($validated['contact_phone'])) {
            $cp1 = $request->input('contact_phone_part1', '');
            $cp2 = $request->input('contact_phone_part2', '');
            $cp3 = $request->input('contact_phone_part3', '');
            $cparts = array_filter([$cp1, $cp2, $cp3], function ($v) { return trim((string)$v) !== ''; });
            if (!empty($cparts)) {
                $validated['contact_phone'] = implode('-', $cparts);
            }
        }
        if (empty($validated['address_postal'])) {
            $ap1 = $request->input('address_postal_part1', '');
            $ap2 = $request->input('address_postal_part2', '');
            $aparts = array_filter([$ap1, $ap2], function ($v) { return trim((string)$v) !== ''; });
            if (!empty($aparts)) {
                $validated['address_postal'] = implode('-', $aparts);
            }
        }
        if (empty($validated['contact_postal'])) {
            $cap1 = $request->input('contact_postal_part1', '');
            $cap2 = $request->input('contact_postal_part2', '');
            $caparts = array_filter([$cap1, $cap2], function ($v) { return trim((string)$v) !== ''; });
            if (!empty($caparts)) {
                $validated['contact_postal'] = implode('-', $caparts);
            }
        }

        // basic post-validation checks
        if (!empty($validated['phone']) && !preg_match('/^[0-9-]+$/', $validated['phone'])) {
            return back()->withInput()->withErrors(['phone' => '電話番号は数字とハイフンのみ使用できます']);
        }
        if (!empty($validated['contact_phone']) && !preg_match('/^[0-9-]+$/', $validated['contact_phone'])) {
            return back()->withInput()->withErrors(['contact_phone' => '連絡先電話は数字とハイフンのみ使用できます']);
        }

        // authorization: only owner or admin may update
        $user = $request->user();
        $isOwner = ($user && $resume->user_id && $resume->user_id === $user->id);
        $isAdmin = ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
        if (! ($isOwner || $isAdmin)) {
            abort(403, 'この履歴書を更新する権限がありません');
        }

        $resume->update($validated);

        // replace histories/licenses/profile: delete existing and recreate from request
        $resume->histories()->delete();
        $historiesInput = $request->input('histories', []);
        if (!empty($historiesInput)) {
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
        if (!empty($licensesInput)) {
            foreach ($licensesInput as $i => $l) {
                if (empty($l['name'])) continue;
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

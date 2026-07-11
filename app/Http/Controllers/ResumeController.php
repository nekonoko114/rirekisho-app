<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResumeStoreRequest;
use App\Http\Requests\ResumeUpdateRequest;
use App\Models\Resume;
use App\Services\PdfExportService;
use App\Services\ResumeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Modifiers\CoverModifier;

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

    /**
     * Export resumes as CSV. Admins export all; normal users export their own.
     */
    public function export()
    {
        $user = Auth::user();

        $query = Resume::orderBy('id', 'desc');

        $isAdminUser = ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
        if (! $isAdminUser) {
            if (! $user) {
                return redirect()->route('login');
            }
            $query->where('user_id', $user->id);
        }

        $resumes = $query->get();

        $filename = 'resumes-'.date('YmdHis').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($resumes) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fwrite($out, "\xEF\xBB\xBF");

            // header row: include main resume fields (omit 'status' and aggregated relation strings)
            fputcsv($out, [
                'ID', '氏名', 'フリガナ', '生年月日', '性別', '電話番号', '連絡先電話番号',
                'メール', '郵便番号', '住所', '連絡先郵便番号', '連絡先住所',
                '作成日', '更新日', '公開トークン', '確認日時', '確認者ID',
            ]);

            foreach ($resumes as $r) {
                $profile = $r->profile;

                fputcsv($out, [
                    $r->id,
                    $r->name,
                    $r->furigana ?? '',
                    optional($r->birth_date)->format('Y-m-d') ?? '',
                    $r->gender ?? '',
                    $r->phone ?? '',
                    $r->contact_phone ?? '',
                    $r->email ?? ($r->user ? $r->user->email : ''),
                    $r->address_postal ?? '',
                    $r->address ?? '',
                    $r->contact_postal ?? '',
                    $r->contact_address ?? '',
                    optional($r->created_at)->format('Y-m-d H:i:s') ?? '',
                    optional($r->updated_at)->format('Y-m-d H:i:s') ?? '',
                    $r->public_token ?? '',
                    optional($r->reviewed_at)->format('Y-m-d H:i:s') ?? '',
                    $r->reviewed_by ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(ResumeStoreRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            // optimize image (fit to 800x800, quality 85) and store under public disk
            $driverClass = extension_loaded('imagick') ? \Intervention\Image\Drivers\Imagick\Driver::class : \Intervention\Image\Drivers\Gd\Driver::class;
            Log::debug('Image driverClass (store): '.$driverClass);
            $manager = new ImageManager($driverClass);
            $img = $manager->read($request->file('photo')->getRealPath());
            // Use CoverModifier to crop/resize to portrait (300x420) centered
            $img->modify(new CoverModifier(300, 420, 'center'));
            $filename = 'photos/'.uniqid('', true).'.jpg';
            $full = storage_path('app/public/'.$filename);
            $img->save($full, 85);
            $validated['photo_path'] = $filename;
        }

        // Attach user_id if authenticated, otherwise generate a public token
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        } else {
            // generate unique public token with collision avoidance
            do {
                $token = bin2hex(random_bytes(16));
            } while (Resume::where('public_token', $token)->exists());
            $validated['public_token'] = $token;
        }

        $resume = Resume::create($validated);

        // delegate creation of histories/licenses/profile to service
        app(ResumeService::class)->createFromRequest($resume, $request);

        if (! $request->user()) {
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
            $this->authorize('view', $resume);

            return view('resume.show', compact('resume'));
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
            $this->authorize('view', $resume);
        } else {
            if (! ($resume->public_token && $token && \hash_equals($resume->public_token, $token))) {
                return redirect()->route('login');
            }
        }

        // Render the same view but instruct it we're rendering for PDF (disable print button)
        $html = view('resume.show', ['resume' => $resume, 'forPdf' => true])->render();

        return app(PdfExportService::class)->respond($html, 'resume-'.$resume->id.'.pdf');
    }

    public function edit(Resume $resume)
    {
        $user = Auth::user();
        $this->authorize('update', $resume);

        $resume->load(['histories', 'licenses', 'profile']);

        return view('resume.edit', compact('resume'));
    }

    public function update(ResumeUpdateRequest $request, Resume $resume)
    {
        $this->authorize('update', $resume);

        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            // remove old photo if present
            if ($resume->photo_path) {
                Storage::disk('public')->delete($resume->photo_path);
            }
            $driverClass = extension_loaded('imagick') ? \Intervention\Image\Drivers\Imagick\Driver::class : \Intervention\Image\Drivers\Gd\Driver::class;
            Log::debug('Image driverClass (update): '.$driverClass);
            $manager = new ImageManager($driverClass);
            $img = $manager->read($request->file('photo')->getRealPath());
            // Use CoverModifier to crop/resize to portrait (300x420) centered
            $img->modify(new CoverModifier(300, 420, 'center'));
            $filename = 'photos/'.uniqid('', true).'.jpg';
            $full = storage_path('app/public/'.$filename);
            $img->save($full, 85);
            $validated['photo_path'] = $filename;
        }

        $resume->update($validated);

        // delegate related updates
        app(ResumeService::class)->updateFromRequest($resume, $request);

        return redirect()->route('resumes.show', $resume)->with('status', '履歴書を更新しました');
    }

    /**
     * Revoke public token for a resume (owner or admin only)
     */
    public function revokePublic(Resume $resume)
    {

        $this->authorize('revokePublic', $resume);
        $resume->public_token = null;
        $resume->save();

        return redirect()->back()->with('status', '公開リンクを無効化しました');
    }

    public function destroy(Resume $resume)
    {
        $this->authorize('delete', $resume);

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

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPublicDocuments;
use App\Http\Requests\ResumeStoreRequest;
use App\Http\Requests\ResumeUpdateRequest;
use App\Models\Resume;
use App\Services\PdfExportService;
use App\Services\ResumePhotoService;
use App\Services\ResumeService;
use App\Support\CsvResponse;
use Illuminate\Support\Facades\Auth;

class ResumeController extends Controller
{
    use AuthorizesPublicDocuments;

    public function create()
    {
        return view('resume.create');
    }

    public function index()
    {
        $user = Auth::user();

        $query = Resume::withCount(['histories', 'licenses'])->orderBy('id', 'desc');

        // If user is not admin, limit to their own resumes
        if (! $user->isAdmin()) {
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
        if (! $user) {
            return redirect()->route('login');
        }

        $query = Resume::orderBy('id', 'desc');
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $rows = $query->get()->map(fn (Resume $r) => [
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

        return CsvResponse::stream('resumes-'.date('YmdHis').'.csv', [
            'ID', '氏名', 'フリガナ', '生年月日', '性別', '電話番号', '連絡先電話番号',
            'メール', '郵便番号', '住所', '連絡先郵便番号', '連絡先住所',
            '作成日', '更新日', '公開トークン', '確認日時', '確認者ID',
        ], $rows);
    }

    public function store(ResumeStoreRequest $request, ResumePhotoService $photos)
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $photos->store($request->file('photo'));
        }

        // Attach user_id if authenticated, otherwise generate a public token
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        } else {
            $validated['public_token'] = Resume::generateUniquePublicToken();
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

        if ($redirect = $this->authorizePublicView($resume)) {
            return $redirect;
        }

        return view('resume.show', compact('resume'));
    }

    /**
     * Generate PDF for a resume using snappy (if installed).
     */
    public function pdf(Resume $resume)
    {
        // Reuse the same authorization logic as show
        if ($redirect = $this->authorizePublicView($resume)) {
            return $redirect;
        }

        // Render the same view but instruct it we're rendering for PDF (disable print button)
        $html = view('resume.show', ['resume' => $resume, 'forPdf' => true])->render();

        return app(PdfExportService::class)->respond($html, 'resume-'.$resume->id.'.pdf');
    }

    public function edit(Resume $resume)
    {
        $this->authorize('update', $resume);

        $resume->load(['histories', 'licenses', 'profile']);

        return view('resume.edit', compact('resume'));
    }

    public function update(ResumeUpdateRequest $request, Resume $resume, ResumePhotoService $photos)
    {
        $this->authorize('update', $resume);

        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $photos->replace($request->file('photo'), $resume->photo_path);
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
        $resume->revokePublicToken();

        return redirect()->back()->with('status', '公開リンクを無効化しました');
    }

    public function destroy(Resume $resume, ResumePhotoService $photos)
    {
        $this->authorize('delete', $resume);

        // delete related resources
        $photos->delete($resume->photo_path);
        $resume->histories()->delete();
        $resume->licenses()->delete();
        $resume->profile()->delete();
        $resume->delete();

        return redirect()->route('resumes.index')->with('status', '履歴書を削除しました');
    }
}

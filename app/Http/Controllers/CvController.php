<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPublicDocuments;
use App\Http\Requests\CvStoreRequest;
use App\Http\Requests\CvUpdateRequest;
use App\Models\Cv;
use App\Services\CvService;
use App\Services\PdfExportService;

class CvController extends Controller
{
    use AuthorizesPublicDocuments;

    public function create()
    {
        return view('cv.create');
    }

    public function store(CvStoreRequest $request)
    {
        $validated = $request->validated();

        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        } else {
            $validated['public_token'] = Cv::generateUniquePublicToken();
        }

        $cv = Cv::create($validated);

        app(CvService::class)->createFromRequest($cv, $request);

        if (! $request->user()) {
            return redirect()->route('cvs.show', ['cv' => $cv->id, 'token' => $cv->public_token])->with('status', '職務経歴書を保存しました');
        }

        return redirect()->route('cvs.show', $cv)->with('status', '職務経歴書を保存しました');
    }

    public function show(Cv $cv)
    {
        $cv->load(['histories', 'licenses']);

        if ($redirect = $this->authorizePublicView($cv)) {
            return $redirect;
        }

        return view('cv.show', compact('cv'));
    }

    public function pdf(Cv $cv)
    {
        // Reuse the same authorization logic as show
        if ($redirect = $this->authorizePublicView($cv)) {
            return $redirect;
        }

        $html = view('cv.show', ['cv' => $cv, 'forPdf' => true])->render();

        return app(PdfExportService::class)->respond($html, 'cv-'.$cv->id.'.pdf', 0.75, 'Portrait');
    }

    public function edit(Cv $cv)
    {
        $this->authorize('update', $cv);

        $cv->load(['histories', 'licenses']);

        return view('cv.edit', compact('cv'));
    }

    public function update(CvUpdateRequest $request, Cv $cv)
    {
        $this->authorize('update', $cv);

        $validated = $request->validated();
        $cv->update($validated);

        app(CvService::class)->updateFromRequest($cv, $request);

        if (auth()->user() && auth()->user()->isAdmin()) {
            return redirect()->route('admin.cvs.show', $cv)->with('status', '職務経歴書を更新しました');
        }

        return redirect()->route('cvs.show', $cv)->with('status', '職務経歴書を更新しました');
    }

    public function revokePublic(Cv $cv)
    {
        $this->authorize('revokePublic', $cv);
        $cv->revokePublicToken();

        return redirect()->back()->with('status', '公開リンクを無効化しました');
    }

    public function destroy(Cv $cv)
    {
        $this->authorize('delete', $cv);

        $cv->histories()->delete();
        $cv->licenses()->delete();
        $cv->delete();

        return redirect()->route('dashboard')->with('status', '職務経歴書を削除しました');
    }
}

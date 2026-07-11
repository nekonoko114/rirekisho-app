<?php

namespace App\Http\Controllers;

use App\Http\Requests\CvStoreRequest;
use App\Http\Requests\CvUpdateRequest;
use App\Models\Cv;
use App\Services\CvService;
use App\Services\PdfExportService;
use Illuminate\Support\Facades\Auth;

class CvController extends Controller
{
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
            do {
                $token = bin2hex(random_bytes(16));
            } while (Cv::where('public_token', $token)->exists());
            $validated['public_token'] = $token;
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

        $user = Auth::user();
        $token = request()->query('token');

        if ($user) {
            if ($cv->user_id !== $user->id && ! ($user && method_exists($user, 'isAdmin') && $user->isAdmin())) {
                abort(403);
            }

            return view('cv.show', compact('cv'));
        }

        if ($cv->public_token && $token && \hash_equals($cv->public_token, $token)) {
            return view('cv.show', compact('cv'));
        }

        return redirect()->route('login');
    }

    public function pdf(Cv $cv)
    {
        $user = Auth::user();
        $token = request()->query('token');

        if ($user) {
            if ($cv->user_id !== $user->id && ! ($user && method_exists($user, 'isAdmin') && $user->isAdmin())) {
                abort(403);
            }
        } else {
            if (! ($cv->public_token && $token && \hash_equals($cv->public_token, $token))) {
                return redirect()->route('login');
            }
        }

        $html = view('cv.show', ['cv' => $cv, 'forPdf' => true])->render();

        return app(PdfExportService::class)->respond($html, 'cv-'.$cv->id.'.pdf');
    }

    public function edit(Cv $cv)
    {
        $user = Auth::user();
        if ($cv->user_id !== $user->id) {
            abort(403);
        }

        $cv->load(['histories', 'licenses']);

        return view('cv.edit', compact('cv'));
    }

    public function update(CvUpdateRequest $request, Cv $cv)
    {
        $user = Auth::user();
        if ($cv->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validated();
        $cv->update($validated);

        app(CvService::class)->updateFromRequest($cv, $request);

        return redirect()->route('cvs.show', $cv)->with('status', '職務経歴書を更新しました');
    }

    public function revokePublic(Cv $cv)
    {
        $user = Auth::user();
        if ($cv->user_id !== $user->id && ! ($user && method_exists($user, 'isAdmin') && $user->isAdmin())) {
            abort(403);
        }

        $cv->public_token = null;
        $cv->save();

        return redirect()->back()->with('status', '公開リンクを無効化しました');
    }

    public function destroy(Cv $cv)
    {
        $user = Auth::user();
        if ($cv->user_id !== $user->id) {
            abort(403);
        }

        $cv->histories()->delete();
        $cv->licenses()->delete();
        $cv->delete();

        return redirect()->route('dashboard')->with('status', '職務経歴書を削除しました');
    }
}

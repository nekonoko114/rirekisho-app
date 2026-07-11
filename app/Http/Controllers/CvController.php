<?php

namespace App\Http\Controllers;

use App\Http\Requests\CvStoreRequest;
use App\Http\Requests\CvUpdateRequest;
use App\Models\Cv;
use App\Services\CvService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            if ($cv->user_id !== $user->id && !($user && method_exists($user, 'isAdmin') && $user->isAdmin())) {
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
            if ($cv->user_id !== $user->id && !($user && method_exists($user, 'isAdmin') && $user->isAdmin())) {
                abort(403);
            }
        } else {
            if (! ($cv->public_token && $token && \hash_equals($cv->public_token, $token))) {
                return redirect()->route('login');
            }
        }

        $html = view('cv.show', ['cv' => $cv, 'forPdf' => true])->render();

        $filename = 'cv-'.$cv->id.'.pdf';
        $pdfZoom = 0.75;

        $pdfServiceEnabled = config('services.pdf.enabled', false);

        if ($pdfServiceEnabled) {
            try {
                $externalPdf = app(\App\Services\ExternalPdfService::class);
                $pdfContent = $externalPdf->generateFromHtml($html, [
                    'filename' => $filename,
                    'zoom' => $pdfZoom,
                ]);

                if ($pdfContent) {
                    return response($pdfContent, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('External PDF generation failed for cv '.$cv->id.': '.$e->getMessage());
            }
        }

        try {
            $useSnappy = app()->bound('snappy.pdf') && class_exists('\Knp\\Snappy\\Pdf');
        } catch (\Throwable $e) {
            $useSnappy = false;
        }

        if ($useSnappy) {
            try {
                $pdf = app('snappy.pdf.wrapper')->loadHTML($html);
                try {
                    $pdf->setOption('zoom', $pdfZoom);
                } catch (\Throwable $e) {
                    Log::warning('Unable to set snappy zoom option: '.$e->getMessage());
                }

                return response($pdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$filename.'"',
                ]);
            } catch (\Throwable $e) {
                Log::error('PDF generation (snappy) failed for cv '.$cv->id.': '.$e->getMessage());
            }
        }

        try {
            $cfg = config('snappy.pdf.options', []);
            $cfg['zoom'] = $pdfZoom;
            config(['snappy.pdf.options' => $cfg]);

            $generator = new \App\Services\PdfGenerator;
            $pdfContent = $generator->outputFromHtml($html);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]);
        } catch (\Throwable $e) {
            Log::error('PDF generation fallback failed for cv '.$cv->id.': '.$e->getMessage());

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-PDF-Error' => 'true',
            ]);
        }
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
        if ($cv->user_id !== $user->id && !($user && method_exists($user, 'isAdmin') && $user->isAdmin())) {
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

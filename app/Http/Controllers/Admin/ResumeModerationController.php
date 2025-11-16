<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResumeModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $q = $request->get('q');
        $query = Resume::with('profile')->orderBy('created_at', 'desc');
        // default to show unreviewed resumes (reviewed_at IS NULL)
        $query->whereNull('reviewed_at');
        if ($q) {
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%");
            });
        }
        $resumes = $query->paginate(20)->withQueryString();

        return view('admin.resumes.index', compact('resumes', 'q'));
    }

    public function show(Resume $resume)
    {
        $resume->load(['histories', 'licenses', 'profile']);

        return view('admin.resumes.show', compact('resume'));
    }

    public function approve(Resume $resume)
    {
        $resume->status = 'approved';
        if (! $resume->public_token) {
            $resume->public_token = bin2hex(\random_bytes(16));
        }
        $resume->reviewed_at = now();
        $resume->reviewed_by = Auth::id();
        $resume->save();

        // activity log
        activity()->causedBy(Auth::user())->performedOn($resume)->withProperties(['action' => 'approve'])->log('Resume approved and marked reviewed');

        return redirect()->back()->with('status', '履歴書を承認しました（公開リンクを発行）');
    }

    public function reject(Resume $resume)
    {
        $resume->status = 'rejected';
        $resume->reviewed_at = now();
        $resume->reviewed_by = Auth::id();
        $resume->save();

        activity()->causedBy(Auth::user())->performedOn($resume)->withProperties(['action' => 'reject'])->log('Resume rejected and marked reviewed');

        return redirect()->back()->with('status', '履歴書を却下しました');
    }

    public function markReviewed(Resume $resume)
    {
        $resume->reviewed_at = now();
        $resume->reviewed_by = Auth::id();
        $resume->save();

        activity()->causedBy(Auth::user())->performedOn($resume)->withProperties(['action' => 'mark_reviewed'])->log('Resume marked reviewed');

        return redirect()->back()->with('status', '履歴書を確認済みにしました');
    }
}

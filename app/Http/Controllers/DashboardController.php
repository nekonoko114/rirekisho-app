<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Basic stats for the logged-in user
        $resumeCount = Resume::where('user_id', $user->id)->count();
        $recentResumes = Resume::where('user_id', $user->id)->orderBy('created_at', 'desc')->take(5)->get();
        $publicResumes = Resume::where('user_id', $user->id)->whereNotNull('public_token')->orderBy('created_at', 'desc')->get();

        // Admin widgets
        $adminTotals = null;
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            $adminTotals = [
                'users' => User::count(),
                'resumes' => Resume::count(),
            ];

            // count of unreviewed resumes
            $adminTotals['unreviewed'] = Resume::whereNull('reviewed_at')->count();

            // prepare simple time-series for the last 7 and 30 days for resumes and users
            $days7 = collect();
            $days30 = collect();
            $today = Carbon::today();

            $resumes7 = [];
            $users7 = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = $today->copy()->subDays($i);
                $resumes7[] = Resume::whereDate('created_at', $d)->count();
                $users7[] = User::whereDate('created_at', $d)->count();
            }

            $resumes30 = [];
            $users30 = [];
            for ($i = 29; $i >= 0; $i--) {
                $d = $today->copy()->subDays($i);
                $resumes30[] = Resume::whereDate('created_at', $d)->count();
                $users30[] = User::whereDate('created_at', $d)->count();
            }

            $adminTotals['series'] = [
                'labels7' => collect(range(6,0))->map(fn($i) => $today->copy()->subDays($i)->format('m/d'))->toArray(),
                'resumes7' => $resumes7,
                'users7' => $users7,
                'labels30' => collect(range(29,0))->map(fn($i) => $today->copy()->subDays($i)->format('m/d'))->toArray(),
                'resumes30' => $resumes30,
                'users30' => $users30,
            ];
        }

        return view('dashboard', compact('resumeCount', 'recentResumes', 'publicResumes', 'adminTotals'));
    }
}

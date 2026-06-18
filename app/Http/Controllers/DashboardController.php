<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            // Efficiently fetch counts grouped by date to avoid N+1 queries
            $start7 = $today->copy()->subDays(6)->startOfDay();
            $end7 = $today->copy()->endOfDay();
            $resumes7Grouped = Resume::selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
                ->whereBetween('created_at', [$start7, $end7])
                ->groupBy('date')
                ->pluck('cnt', 'date')
                ->toArray();

            $users7Grouped = User::selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
                ->whereBetween('created_at', [$start7, $end7])
                ->groupBy('date')
                ->pluck('cnt', 'date')
                ->toArray();

            $start30 = $today->copy()->subDays(29)->startOfDay();
            $end30 = $today->copy()->endOfDay();
            $resumes30Grouped = Resume::selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
                ->whereBetween('created_at', [$start30, $end30])
                ->groupBy('date')
                ->pluck('cnt', 'date')
                ->toArray();

            $users30Grouped = User::selectRaw('DATE(created_at) as date, COUNT(*) as cnt')
                ->whereBetween('created_at', [$start30, $end30])
                ->groupBy('date')
                ->pluck('cnt', 'date')
                ->toArray();

            $labels7 = [];
            $resumes7 = [];
            $users7 = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = $today->copy()->subDays($i)->format('Y-m-d');
                $labels7[] = $today->copy()->subDays($i)->format('m/d');
                $resumes7[] = isset($resumes7Grouped[$d]) ? (int) $resumes7Grouped[$d] : 0;
                $users7[] = isset($users7Grouped[$d]) ? (int) $users7Grouped[$d] : 0;
            }

            $labels30 = [];
            $resumes30 = [];
            $users30 = [];
            for ($i = 29; $i >= 0; $i--) {
                $d = $today->copy()->subDays($i)->format('Y-m-d');
                $labels30[] = $today->copy()->subDays($i)->format('m/d');
                $resumes30[] = isset($resumes30Grouped[$d]) ? (int) $resumes30Grouped[$d] : 0;
                $users30[] = isset($users30Grouped[$d]) ? (int) $users30Grouped[$d] : 0;
            }

            $adminTotals['series'] = [
                'labels7' => $labels7,
                'resumes7' => $resumes7,
                'users7' => $users7,
                'labels30' => $labels30,
                'resumes30' => $resumes30,
                'users30' => $users30,
            ];
        }

        return view('dashboard', compact('resumeCount', 'recentResumes', 'publicResumes', 'adminTotals'));
    }
}

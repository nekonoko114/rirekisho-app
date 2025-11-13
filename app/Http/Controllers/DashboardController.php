<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\User;
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
        }

        return view('dashboard', compact('resumeCount', 'recentResumes', 'publicResumes', 'adminTotals'));
    }
}

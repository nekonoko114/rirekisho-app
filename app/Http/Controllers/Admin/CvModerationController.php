<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use Illuminate\Http\Request;

class CvModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $q = $request->get('q');
        $query = Cv::with('user')->orderBy('created_at', 'desc');

        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }

        $cvs = $query->paginate(20)->withQueryString();

        return view('admin.cvs.index', compact('cvs', 'q'));
    }

    public function show(Cv $cv)
    {
        $cv->load(['histories', 'licenses', 'user']);

        return view('admin.cvs.show', compact('cv'));
    }

    public function destroy(Cv $cv)
    {
        $cv->histories()->delete();
        $cv->licenses()->delete();
        $cv->delete();

        return redirect()->route('admin.cvs.index')->with('status', '職務経歴書を削除しました');
    }
}

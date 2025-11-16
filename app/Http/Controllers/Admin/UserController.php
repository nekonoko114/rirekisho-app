<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $q = $request->get('q');
        $users = User::query();
        if ($q) {
            $users->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }
        $users = $users->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|max:50',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = $data['role'] ?? 'user';

        $user = User::create($data);

        // If Spatie roles available, assign role
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole($data['role']);
            } catch (\Throwable $e) {
            }
        }

        return redirect()->route('admin.users.index')->with('status', 'ユーザーを作成しました');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|max:50',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['role'] = $data['role'] ?? ($user->role ?? 'user');

        $user->update($data);

        if (method_exists($user, 'syncRoles')) {
            try {
                $user->syncRoles([$data['role']]);
            } catch (\Throwable $e) {
            }
        }

        return redirect()->route('admin.users.index')->with('status', 'ユーザーを更新しました');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'ユーザーを削除しました');
    }
}

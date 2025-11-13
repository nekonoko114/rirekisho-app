@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-3">ユーザー編集</h1>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">名前</label>
            <input name="name" value="{{ old('name', $user->name) }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">メール</label>
            <input name="email" value="{{ old('email', $user->email) }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">パスワード（変更する場合のみ）</label>
            <input name="password" type="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">ロール</label>
            <select name="role" class="form-select">
                <option value="user" {{ (old('role', $user->role) === 'user') ? 'selected' : '' }}>user</option>
                <option value="admin" {{ (old('role', $user->role) === 'admin') ? 'selected' : '' }}>admin</option>
                <option value="moderator" {{ (old('role', $user->role) === 'moderator') ? 'selected' : '' }}>moderator</option>
            </select>
        </div>

        <div>
            <button class="btn btn-primary">保存</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-link">戻る</a>
        </div>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>ユーザーを編集</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">名前</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">メール</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">パスワード（変更する場合のみ入力）</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">パスワード（確認）</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">ロール</label>
            <select name="role" class="form-select">
                <option value="user" {{ (old('role', $user->role) === 'user') ? 'selected' : '' }}>User</option>
                <option value="admin" {{ (old('role', $user->role) === 'admin') ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        <button class="btn btn-primary">更新する</button>
    </form>
</div>
@endsection

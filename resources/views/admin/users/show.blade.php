@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-3">ユーザー詳細</h1>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $user->id }}</p>
            <p><strong>名前:</strong> {{ $user->name }}</p>
            <p><strong>メール:</strong> {{ $user->email }}</p>
            <p><strong>ロール:</strong> {{ $user->role ?? 'user' }}</p>
            <p><strong>登録日:</strong> {{ optional($user->created_at)->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">編集</a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-link">戻る</a>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>ユーザー詳細</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $user->id }}</p>
            <p><strong>名前:</strong> {{ $user->name }}</p>
            <p><strong>メール:</strong> {{ $user->email }}</p>
            <p><strong>ロール:</strong> {{ $user->role }}</p>
            <p><strong>作成日:</strong> {{ $user->created_at }}</p>
        </div>
    </div>

    <div class="mt-3">
        @if(Route::has('admin.users.edit'))
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">編集</a>
        @endif
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">一覧に戻る</a>
    </div>
</div>
@endsection

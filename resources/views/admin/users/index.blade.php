@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>ユーザー一覧</h2>
        @if(Route::has('admin.users.create'))
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">新しいユーザーを追加</a>
        @endif
    </div>

    <form class="mb-3" method="GET" action="{{ route('admin.users.index') }}">
        <div class="input-group mb-3">
            <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="名前かメールで検索">
            <button class="btn btn-outline-secondary">検索</button>
        </div>
    </form>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>メール</th>
                    <th>ロール</th>
                    <th>作成日</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role ?? 'user' }}</td>
                    <td>{{ optional($user->created_at)->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">表示</a>
                        @if(Route::has('admin.users.edit'))
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary ms-1">編集</a>
                        @endif
                        @if(Route::has('admin.users.destroy'))
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('ユーザーを削除します。よろしいですか？');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">削除</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>
@endsection

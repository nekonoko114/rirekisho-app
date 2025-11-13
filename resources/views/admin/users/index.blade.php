@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-3">ユーザー管理</h1>

    <form class="mb-3" method="GET" action="{{ route('admin.users.index') }}">
        <div class="input-group">
            <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="名前かメールで検索">
            <button class="btn btn-outline-secondary">検索</button>
        </div>
    </form>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名前</th>
                    <th>メール</th>
                    <th>ロール</th>
                    <th>登録日</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->role ?? 'user' }}</td>
                        <td>{{ optional($u->created_at)->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.show', $u) }}" class="btn btn-sm btn-outline-secondary">表示</a>
                            <a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-outline-primary ms-1">編集</a>
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline ms-1" onsubmit="return confirm('このユーザーを削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">削除</button>
                            </form>
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
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>ユーザー一覧</h2>
        @if(Route::has('admin.users.create'))
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">新しいユーザーを追加</a>
        @endif
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <table class="table table-striped">
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
                <td>{{ $user->role }}</td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td class="text-end">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">表示</a>
                    @if(Route::has('admin.users.edit'))
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">編集</a>
                    @endif
                    @if(Route::has('admin.users.destroy'))
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline-block" onsubmit="return confirm('ユーザーを削除します。よろしいですか？');">
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

    {{ $users->links() }}
</div>
@endsection

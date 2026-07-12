@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-3">職務経歴書一覧</h1>

    <div class="mb-3 d-flex gap-2">
        <form class="flex-grow-1" method="GET" action="{{ route('admin.cvs.index') }}">
            <div class="input-group">
                <input type="search" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="名前で検索">
                <button class="btn btn-outline-secondary">検索</button>
            </div>
        </form>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>氏名</th>
                    <th>作成者</th>
                    <th>希望職種</th>
                    <th>作成日</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($cvs as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->user ? $c->user->email : 'ゲスト' }}</td>
                        <td>{{ $c->desired_position ?? '未設定' }}</td>
                        <td>{{ optional($c->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.cvs.show', $c) }}" class="btn btn-sm btn-outline-secondary">表示</a>
                            <a href="{{ route('cvs.edit', $c) }}" class="btn btn-sm btn-outline-primary ms-1">編集</a>
                            <form method="POST" action="{{ route('admin.cvs.destroy', $c) }}" class="d-inline ms-1" onsubmit="return confirm('この職務経歴書を削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">削除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $cvs->links() }}</div>
</div>
@endsection

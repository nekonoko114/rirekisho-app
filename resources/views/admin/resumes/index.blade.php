@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-3">履歴書モデレーション（未処理）</h1>

    <form class="mb-3" method="GET" action="{{ route('admin.resumes.index') }}">
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
                    <th>氏名</th>
                    <th>作成者</th>
                    <th>作成日</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumes as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->email ?? ($r->user ? $r->user->email : 'ゲスト') }}</td>
                        <td>{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.resumes.show', $r) }}" class="btn btn-sm btn-outline-secondary">表示</a>
                            <form method="POST" action="{{ route('admin.resumes.mark_reviewed', $r) }}" class="d-inline ms-1">
                                @csrf
                                <button class="btn btn-sm btn-success">確認済みにする</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $resumes->links() }}</div>
</div>
@endsection

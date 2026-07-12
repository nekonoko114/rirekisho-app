@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-3">職務経歴書管理 — ID: {{ $cv->id }}</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">{{ $cv->name }}</h5>
            <p class="text-muted">作成日: {{ optional($cv->created_at)->format('Y-m-d H:i') }}</p>
            <p>作成者メール: {{ $cv->user ? $cv->user->email : 'ゲスト' }}</p>
            <p>希望職種: <strong>{{ $cv->desired_position ?? '未設定' }}</strong></p>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('cvs.show', ['cv' => $cv->id, 'token' => $cv->public_token]) }}" target="_blank" class="btn btn-outline-dark">公開ページを表示</a>
        <a href="{{ route('cvs.pdf', ['cv' => $cv->id, 'token' => $cv->public_token]) }}" target="_blank" class="btn btn-outline-secondary ms-1">PDF プレビュー</a>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('cvs.edit', $cv) }}" class="btn btn-primary">編集する</a>

        <form method="POST" action="{{ route('admin.cvs.destroy', $cv) }}" onsubmit="return confirm('この職務経歴書を削除しますか？');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">削除する</button>
        </form>
    </div>

    <hr>

    <h5>志望の動機・自己PR</h5>
    <div class="p-3 bg-light border rounded mb-4 text-dark">
        {!! nl2br(e($cv->motivation)) !!}
    </div>

    <h5>職務経歴</h5>
    @if($cv->histories->count() > 0)
        @foreach($cv->histories as $history)
            <div class="card mb-2 text-dark">
                <div class="card-header py-2 font-weight-bold">
                    {{ $history->start_year }}年 {{ $history->start_month }}月 〜 
                    @if($history->end_year && $history->end_month)
                        {{ $history->end_year }}年 {{ $history->end_month }}月
                    @else
                        現在
                    @endif
                </div>
                <div class="card-body py-2">
                    <p class="mb-1"><strong>会社名:</strong> {{ $history->company_name }}</p>
                    <p class="mb-0 text-muted">{!! nl2br(e($history->job_description)) !!}</p>
                </div>
            </div>
        @endforeach
    @else
        <p class="text-muted">職務経歴の登録はありません。</p>
    @endif

    <h5 class="mt-4">免許・資格</h5>
    @if($cv->licenses->count() > 0)
        <ul class="list-group text-dark">
            @foreach($cv->licenses as $license)
                <li class="list-group-item">
                    <strong>{{ $license->year }}年 {{ $license->month }}月:</strong> {{ $license->name }}
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-muted">免許・資格の登録はありません。</p>
    @endif

    <div class="mt-4">
        <a href="{{ route('admin.cvs.index') }}" class="btn btn-link">一覧へ戻る</a>
    </div>
</div>
@endsection

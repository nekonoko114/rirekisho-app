@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="h4 mb-3">履歴書モデレーション — ID: {{ $resume->id }}</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">{{ $resume->name }}</h5>
            <p class="text-muted">作成日: {{ optional($resume->created_at)->format('Y-m-d H:i') }}</p>
            <p>メール: {{ $resume->email ?? ($resume->user ? $resume->user->email : 'ゲスト') }}</p>
            <p>ステータス: <strong>{{ $resume->status }}</strong></p>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('resumes.show', ['resume' => $resume->id, 'token' => $resume->public_token]) }}" target="_blank" class="btn btn-outline-dark">公開ページを表示</a>
        <a href="{{ route('resumes.pdf', $resume) }}" target="_blank" class="btn btn-outline-secondary ms-1">PDF プレビュー</a>
    </div>

    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.resumes.approve', $resume) }}">
            @csrf
            <button class="btn btn-success">承認（公開リンク発行）</button>
        </form>

        <form method="POST" action="{{ route('admin.resumes.reject', $resume) }}" onsubmit="return confirm('この履歴書を却下しますか？');">
            @csrf
            <button class="btn btn-danger">却下</button>
        </form>

        @if(!$resume->reviewed_at)
            <form method="POST" action="{{ route('admin.resumes.mark_reviewed', $resume) }}" class="ms-1">
                @csrf
                <button class="btn btn-outline-success">確認済みにする</button>
            </form>
        @else
            <div class="align-self-center ms-2 text-muted">確認済み: {{ optional($resume->reviewed_at)->format('Y-m-d H:i') }}</div>
        @endif
    </div>

    <hr>

    {{-- Show histories/licenses/profile briefly --}}
    <h5>プロフィール</h5>
    <p>{{ $resume->profile->motivation ?? '' }}</p>

    <h5>学歴/職歴</h5>
    <ul>
        @foreach($resume->histories as $h)
            <li>{{ $h->type }}: {{ $h->year }}{{ $h->month ? '/'.$h->month : '' }} — {{ $h->description }}</li>
        @endforeach
    </ul>

    <h5>免許・資格</h5>
    <ul>
        @foreach($resume->licenses as $l)
            <li>{{ $l->year ? $l->year.'/' : '' }}{{ $l->name }}</li>
        @endforeach
    </ul>

    <div class="mt-3">
        <a href="{{ route('admin.resumes.index') }}" class="btn btn-link">一覧へ戻る</a>
    </div>
</div>
@endsection

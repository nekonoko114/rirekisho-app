<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Include Bootstrap 5 CSS from CDN for this page -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-" crossorigin="anonymous">

    <div class="py-4">
        <div class="container">
            <h1 class="h3 mb-4">ダッシュボード</h1>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-muted small">作成済みの履歴書</div>
                            <div class="display-6">{{ $resumeCount ?? 0 }}</div>
                            <div class="mt-3">
                                <a href="{{ route('resumes.create') }}" class="btn btn-primary btn-sm">新規作成</a>
                                <a href="{{ route('resumes.index') }}" class="btn btn-outline-secondary btn-sm ms-2">投稿一覧</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">最近の履歴書（5件）</h5>
                            @if(isset($recentResumes) && $recentResumes->count())
                                <div class="table-responsive">
                                    <table class="table table-sm mt-3 mb-0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>氏名</th>
                                                <th>作成日</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentResumes as $r)
                                                <tr>
                                                    <td>{{ $r->id }}</td>
                                                    <td>{{ $r->name }}</td>
                                                    <td>{{ optional($r->created_at)->format('Y-m-d') }}</td>
                                                    <td>
                                                        <a href="{{ route('resumes.show', $r) }}" class="btn btn-outline-secondary btn-sm me-1">表示</a>
                                                        <a href="{{ route('resumes.edit', $r) }}" class="btn btn-outline-primary btn-sm me-1">編集</a>
                                                        <a href="{{ route('resumes.pdf', $r) }}" target="_blank" class="btn btn-outline-dark btn-sm">PDF</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-muted">まだ履歴書がありません。<a href="{{ route('resumes.create') }}">新規作成</a>してください。</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">公開リンク管理</h5>
                    <p class="text-muted small">トークンを発行している履歴書の公開リンクを一覧・無効化できます。</p>

                    @if(isset($publicResumes) && $publicResumes->count())
                        <div class="table-responsive">
                            <table class="table table-sm mt-3 mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>氏名</th>
                                        <th>公開リンク</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($publicResumes as $pr)
                                        <tr>
                                            <td>{{ $pr->id }}</td>
                                            <td>{{ $pr->name }}</td>
                                            <td style="min-width:320px;">
                                                @php $link = route('resumes.show', ['resume' => $pr->id, 'token' => $pr->public_token]); @endphp
                                                <div class="input-group input-group-sm">
                                                    <input type="text" readonly class="form-control" value="{{ $link }}">
                                                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $link }}')">コピー</button>
                                                </div>
                                            </td>
                                            <td>
                                                <form method="POST" action="{{ route('resumes.revoke_public', $pr) }}" onsubmit="return confirm('公開リンクを無効化しますか？');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">無効化</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">公開リンクはありません。</div>
                    @endif
                </div>
            </div>

            @if(isset($adminTotals) && $adminTotals)
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">管理者情報</h5>
                        <p class="mb-1">ユーザー数: <strong>{{ $adminTotals['users'] }}</strong></p>
                        <p class="mb-0">総履歴書数: <strong>{{ $adminTotals['resumes'] }}</strong></p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

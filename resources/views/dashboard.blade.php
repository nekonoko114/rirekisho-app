<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Bootstrap is bundled via Vite (imported in resources/css/app.css) -->

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
                            <h5 class="card-title">最近の履歴書</h5>
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
                <div class="mb-3">
                    <a href="{{ route('admin.resumes.index') }}" class="btn btn-outline-primary">未確認履歴書を確認する（{{ $adminTotals['unreviewed'] ?? 0 }}）</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">管理者情報</h5>
                        <p class="mb-1">ユーザー数: <strong>{{ $adminTotals['users'] }}</strong></p>
                        <p class="mb-1">総履歴書数: <strong>{{ $adminTotals['resumes'] }}</strong></p>
                        <p class="mb-0">未確認履歴書: <strong>{{ $adminTotals['unreviewed'] ?? 0 }}</strong></p>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">最近の推移（7日）</h5>
                        <div style="height:240px;">
                            <canvas id="chart7"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">最近の推移（30日）</h5>
                        <div style="height:240px;">
                            <canvas id="chart30"></canvas>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

@if(isset($adminTotals['series']))
    @php $s = $adminTotals['series']; @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const labels7 = @json($s['labels7']);
            const resumes7 = @json($s['resumes7']);
            const users7 = @json($s['users7']);

            const ctx7 = document.getElementById('chart7').getContext('2d');
            new Chart(ctx7, {
                type: 'line',
                data: {
                    labels: labels7,
                    datasets: [
                        { label: '履歴書作成', data: resumes7, borderColor: '#0d6efd', tension: 0.2 },
                        { label: 'ユーザー登録', data: users7, borderColor: '#198754', tension: 0.2 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            const labels30 = @json($s['labels30']);
            const resumes30 = @json($s['resumes30']);
            const users30 = @json($s['users30']);
            const ctx30 = document.getElementById('chart30').getContext('2d');
            new Chart(ctx30, {
                type: 'line',
                data: {
                    labels: labels30,
                    datasets: [
                        { label: '履歴書作成', data: resumes30, borderColor: '#0d6efd', tension: 0.2 },
                        { label: 'ユーザー登録', data: users30, borderColor: '#198754', tension: 0.2 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
    </script>
@endif

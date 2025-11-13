<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold mb-6">ダッシュボード</h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="text-sm text-gray-500">作成済みの履歴書</div>
                    <div class="text-3xl font-bold">{{ $resumeCount ?? 0 }}</div>
                    <div class="mt-4">
                        <a href="{{ route('resumes.create') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded">新規作成</a>
                        <a href="{{ route('resumes.index') }}" class="ml-2 inline-block text-gray-700 px-4 py-2">投稿一覧</a>
                    </div>
                </div>

                <div class="md:col-span-2 bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="text-sm text-gray-500">最近の履歴書（5件）</div>
                    @if(isset($recentResumes) && $recentResumes->count())
                        <table class="min-w-full mt-4">
                            <thead>
                                <tr class="text-left text-sm text-gray-600">
                                    <th>ID</th>
                                    <th>氏名</th>
                                    <th>作成日</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentResumes as $r)
                                    <tr class="border-t">
                                        <td class="py-2">{{ $r->id }}</td>
                                        <td class="py-2">{{ $r->name }}</td>
                                        <td class="py-2">{{ optional($r->created_at)->format('Y-m-d') }}</td>
                                        <td class="py-2">
                                            <a href="{{ route('resumes.show', $r) }}" class="px-3 py-1 border rounded text-sm mr-2">表示</a>
                                            @if(auth()->user() && (auth()->user()->isAdmin() ?? false))
                                                <a href="{{ route('resumes.edit', $r) }}" class="px-3 py-1 border rounded text-sm mr-2">編集</a>
                                            @else
                                                <a href="{{ route('resumes.edit', $r) }}" class="px-3 py-1 border rounded text-sm mr-2">編集</a>
                                            @endif
                                            <a href="{{ route('resumes.pdf', $r) }}" target="_blank" class="px-3 py-1 border rounded text-sm">PDF</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="mt-4 text-gray-600">まだ履歴書がありません。<a href="{{ route('resumes.create') }}" class="text-blue-600">新規作成</a>してください。</div>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 shadow-sm sm:rounded-lg mb-6">
                <h2 class="text-lg font-medium">公開リンク管理</h2>
                <p class="text-sm text-gray-500">トークンを発行している履歴書の公開リンクを一覧・無効化できます。</p>

                @if(isset($publicResumes) && $publicResumes->count())
                    <table class="min-w-full mt-4">
                        <thead>
                            <tr class="text-left text-sm text-gray-600">
                                <th>ID</th>
                                <th>氏名</th>
                                <th>公開リンク</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($publicResumes as $pr)
                                <tr class="border-t">
                                    <td class="py-2">{{ $pr->id }}</td>
                                    <td class="py-2">{{ $pr->name }}</td>
                                    <td class="py-2">
                                        @php
                                            $link = route('resumes.show', ['resume' => $pr->id, 'token' => $pr->public_token]);
                                        @endphp
                                        <input type="text" readonly class="w-1/2 p-2 border" value="{{ $link }}" id="public-link-{{ $pr->id }}">
                                        <button onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $link }}')" class="ml-2 px-3 py-1 bg-gray-200 rounded">コピー</button>
                                    </td>
                                    <td class="py-2">
                                        <form method="POST" action="{{ route('resumes.revoke_public', $pr) }}" onsubmit="return confirm('公開リンクを無効化しますか？');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded">無効化</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="mt-4 text-gray-600">公開リンクはありません。</div>
                @endif
            </div>

            @if(isset($adminTotals) && $adminTotals)
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h2 class="text-lg font-medium">管理者情報</h2>
                    <div class="mt-3">ユーザー数: <strong>{{ $adminTotals['users'] }}</strong></div>
                    <div>総履歴書数: <strong>{{ $adminTotals['resumes'] }}</strong></div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>投稿一覧</h2>
    <a href="{{ route('resumes.create') }}" class="btn btn-sm btn-primary">新規作成</a>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>氏名</th>
              <th>電話番号</th>
              <th>連絡先電話番号</th>
              <th>郵便番号</th>
              <th>メールアドレス</th>
              <th>作成日</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($resumes as $r)
              <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->name }}</td>
                <td>{{ $r->phone }}</td>
                <td>{{ $r->contact_phone }}</td>
                <td>{{ $r->address_postal }}</td>
                <td>{{ $r->email }}</td>
                <td>{{ $r->created_at->format('Y-m-d') }}</td>
                <td class="d-flex gap-2">
                  <a href="{{ route('resumes.show', $r) }}" class="btn btn-sm btn-outline-secondary">表示</a>
                  <a href="{{ route('resumes.edit', $r) }}" class="btn btn-sm btn-outline-primary">編集</a>
                  <form method="post" action="{{ route('resumes.destroy', $r) }}" onsubmit="return confirm('本当に削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">削除</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3">
    {{ $resumes->links() }}
  </div>
</div>
@endsection

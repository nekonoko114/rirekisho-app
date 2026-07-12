<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>職務経歴書</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    @if(isset($forPdf) && $forPdf)
      <link rel="stylesheet" href="{{ public_path('css/resume-print.css') }}">
    @else
      <link rel="stylesheet" href="{{ asset('css/resume-print.css') }}">
      @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
      body {
        background-color: #f8f9fa;
        color: #333;
        font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, sans-serif;
      }
      .cv-container {
        max-width: 800px;
        margin: 2rem auto;
        background: #fff;
        padding: 2rem 3rem;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
      }
      .cv-header {
        text-align: center;
        margin-bottom: 2rem;
      }
      .cv-title {
        font-size: 24px;
        letter-spacing: 5px;
        margin-bottom: 10px;
        font-weight: bold;
      }
      .cv-date {
        text-align: right;
        margin-bottom: 20px;
      }
      .cv-name-section {
        margin-bottom: 30px;
        display: flex;
        justify-content: flex-end;
      }
      .cv-name {
        font-size: 20px;
      }
      .cv-section-title {
        border-left: 5px solid #20c997;
        padding-left: 10px;
        margin-top: 30px;
        margin-bottom: 15px;
        font-size: 18px;
        font-weight: bold;
      }
      .history-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
      }
      .history-table th, .history-table td {
        border: 1px solid #ddd;
        padding: 10px;
      }
      .history-table th {
        background-color: #f8f9fa;
        text-align: center;
      }
      .period-col {
        width: 25%;
        text-align: center;
      }
      .license-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
      }
      .license-table th, .license-table td {
        border: 1px solid #ddd;
        padding: 10px;
      }
      .license-table th {
        background-color: #f8f9fa;
        text-align: center;
      }
      .date-col {
        width: 20%;
        text-align: center;
      }
      @page {
        size: A4 portrait;
        margin: 0;
      }
      @media print {
        body { background: #fff; }
        .cv-container {
          box-shadow: none;
          margin: 0 !important;
          padding: 20mm 15mm !important;
          max-width: 100% !important;
          width: 100% !important;
        }
        .no-print { display: none !important; }
      }
      /* ダークモード等のテーマ設定による文字色反転を防ぎ、印刷用紙イメージに合わせて常に黒系統のテキストを表示します */
      .cv-container,
      .cv-container * {
        color: #333 !important;
      }
      .cv-container th {
        background-color: #f8f9fa !important;
        color: #333 !important;
      }
      .cv-container td {
        color: #333 !important;
      }
      .cv-section-title {
        border-left: 5px solid #20c997;
        padding-left: 10px;
        margin-top: 30px;
        margin-bottom: 15px;
        font-size: 18px;
        font-weight: bold;
        color: #333 !important;
      }
    </style>
  </head>
  <body>
    @if(!isset($forPdf) || !$forPdf)
      <div class="container mt-3 mb-3 no-print">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            @if(Auth::check())
              @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.cvs.index') }}" class="btn btn-outline-secondary">職務経歴書一覧へ戻る</a>
              @else
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">ダッシュボードへ戻る</a>
              @endif
              <a href="{{ route('cvs.edit', $cv) }}" class="btn btn-outline-primary ms-2">編集</a>
            @endif
          </div>
          <div>
            @if(Auth::check() || $cv->public_token)
              <a href="{{ route('cvs.pdf', ['cv' => $cv->id, 'token' => request()->query('token')]) }}" target="_blank" class="btn btn-dark">PDF出力</a>
            @endif
            <button onclick="window.print()" class="btn btn-secondary ms-2">印刷</button>
          </div>
        </div>

        <!-- 印刷方法の簡易説明ガイド -->
        <div class="card mb-4 bg-light border-secondary-subtle">
          <div class="card-body py-3 px-4">
            <h6 class="card-title text-dark fw-bold mb-2">【ご案内】印刷・PDF保存方法</h6>
            <ul class="text-secondary small mb-0 ps-3">
              <li class="mb-1"><strong class="text-dark">ブラウザから直接印刷する場合：</strong> 右上の「印刷」ボタンを押すと、お使いのブラウザの印刷設定画面が開きます。プリンターを選択して印刷してください。</li>
              <li class="mb-1"><strong class="text-dark">PDFファイルを印刷する場合：</strong> 「PDF出力」ボタンを押すと、別タブでPDFファイルが開きます。ブラウザのメニュー等にあるダウンロードボタン（下向き矢印のアイコンなど）から端末に保存し、ご自宅のプリンター、またはコンビニのプリントサービス（ネットワークプリント、かんたんnetprintなど）を利用して印刷してください。</li>
              <li><strong class="text-dark">PDFとして保存したい場合：</strong> 「印刷」ボタンを押し、印刷設定画面の「送信先」または「プリンター」で「PDFに保存」を選択して「保存」を実行することでもPDFファイルを作成できます。</li>
            </ul>
          </div>
        </div>
      </div>
    @endif

    <div class="cv-container">
      <div class="cv-header">
        <div class="cv-title">職務経歴書</div>
      </div>
      
      @php
        // 日付を和暦に変換
        $date = $cv->created_at;
        $era = '令和';
        $eraYear = $date->year - 2018;
        $dateStr = $era . $eraYear . '年 ' . $date->month . '月 ' . $date->day . '日 現在';
      @endphp
      <div class="cv-date">{{ $dateStr }}</div>

      <div class="cv-name-section">
        <div>
          氏名： <span class="cv-name">{{ $cv->name }}</span>
        </div>
      </div>

      <!-- 職務経歴 -->
      <div class="cv-section">
        <div class="cv-section-title">職務経歴</div>
        @if($cv->histories->count() > 0)
          @foreach($cv->histories as $history)
            <table class="history-table">
              <tr>
                <th class="period-col">期間</th>
                <td>
                  {{ $history->start_year }}年 {{ $history->start_month }}月 〜 
                  @if($history->end_year && $history->end_month)
                    {{ $history->end_year }}年 {{ $history->end_month }}月
                  @else
                    現在
                  @endif
                </td>
              </tr>
              <tr>
                <th>会社名</th>
                <td>{{ $history->company_name }}</td>
              </tr>
              <tr>
                <th>職務内容・実績</th>
                <td>{!! nl2br(e($history->job_description)) !!}</td>
              </tr>
            </table>
          @endforeach
        @else
          <p>職務経歴の登録はありません。</p>
        @endif
      </div>

      <!-- 免許・資格 -->
      <div class="cv-section">
        <div class="cv-section-title">免許・資格</div>
        @if($cv->licenses->count() > 0)
          <table class="license-table">
            <tr>
              <th class="date-col">年月</th>
              <th>免許・資格</th>
            </tr>
            @foreach($cv->licenses as $license)
              <tr>
                <td class="date-col">{{ $license->year }}年 {{ $license->month }}月</td>
                <td>{{ $license->name }}</td>
              </tr>
            @endforeach
          </table>
        @else
          <p>免許・資格の登録はありません。</p>
        @endif
      </div>

      <!-- 自己PR等 -->
      @if($cv->motivation)
        <div class="cv-section">
          <div class="cv-section-title">自己PR</div>
          <div style="border: 1px solid #ddd; padding: 15px;">
            {!! nl2br(e($cv->motivation)) !!}
          </div>
        </div>
      @endif

      <!-- 希望職種 -->
      @if($cv->desired_position)
        <div class="cv-section">
          <div class="cv-section-title">希望職種</div>
          <div style="border: 1px solid #ddd; padding: 15px;">
            {{ $cv->desired_position }}
          </div>
        </div>
      @endif

    </div>
  </body>
</html>

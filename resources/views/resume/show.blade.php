<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=1200" />
    <link rel="stylesheet" href="{{ asset('css/resume-print.css') }}" />
    @if(isset($forPdf) && $forPdf)
      @php
        $cssPath = public_path('css/resume-print.css');
        $css = file_exists($cssPath) ? file_get_contents($cssPath) : null;
      @endphp
      @if($css)
        <style>{!! $css !!}</style>
      @endif
    @else
      @vite(['resources/js/app.js'])
    @endif
    <title>履歴書プレビュー</title>
  </head>

  <body>
    @unless(isset($forPdf) && $forPdf)
    @php
      $token = request()->query('token') ?? ($resume->public_token ?? null);
      if (Route::has('resumes.pdf')) {
        $pdfUrl = route('resumes.pdf', $resume);
      } else {
        $pdfUrl = url('/resumes/'.$resume->id.'/pdf');
      }
      if ($token) { $pdfUrl .= (str_contains($pdfUrl, '?') ? '&' : '?') . 'token=' . $token; }
    @endphp
    <div style="text-align: right; margin: 8px 30px 0 0; btn">
      <button onclick="downloadResumePDF()" class="print-button pdf-download-btn" style="background: #4CAF50; color: white; border: none; padding: 8px 16px; cursor: pointer; border-radius: 4px;">PDFダウンロード</button>
    </div>
    @endunless
    <div class="rirekisho-container">
      <div class="page page-left">
        <div class="page-heading">
          <h1 class="title">履歴書</h1>
          <div class="date-label">{{ now()->format('Y年n月j日') }} 現在</div>
        </div>

        <div class="date-section">
          @if($resume->photo_path)
            @php
              // Use a relative storage path to avoid absolute-URL host/port mismatches
              // (artisan serve may run on 127.0.0.1:8000 while APP_URL is http://localhost)
              $photoUrl = '/storage/' . ltrim($resume->photo_path, '/');
            @endphp
            <div class="photo-box">
              <img src="{{ $photoUrl }}" alt="履歴書の写真" style="width:100px; height:140px; object-fit:cover; border:1px solid #ccc;" />
            </div>
          @else
            <div class="note-box">
              <p>写真を貼る位置</p>
            </div>
          @endif
        </div>

        <div class="user-name-section">
          <div class="furigana">
            <div class="furigana-label">ふりがな</div>
            <div class="furigana-value">{{ $resume->furigana }}</div>
          </div>
          <div class="name-label">
            <div class="name-label-text">氏　名</div>
            <div class="name-value">{{ $resume->name }}</div>
          </div>
        </div>

        <div class="birth">
      @php
      use Illuminate\Support\Carbon;
      $birth = $resume->birth_date;
      $showBirth = false;
      // normalize to Carbon instance if possible
      if ($birth) {
        try {
          if (! $birth instanceof Carbon) {
            $birth = Carbon::parse($birth);
          }
          $year = (int) $birth->format('Y');
          if ($year >= 1900 && $year <= now()->year) {
            $showBirth = true;
          }
        } catch (\Exception $e) {
          $showBirth = false;
        }
      }
      @endphp
          <div class="birth-wrapper">
              @if($showBirth)
              <span class="birth-date">{{ $birth->format('Y年n月j日') }} 生</span>
              @php
                // use Carbon->age to get integer age (years)
                $age = is_object($birth) && method_exists($birth, 'age') ? (int) $birth->age : (int) now()->diffInYears($birth);
                // ensure age is non-negative (guard against malformed dates or parsing issues)
                $age = abs($age);
              @endphp
              <span class="age">（満　{{ $age }} 歳）</span>
            @else
              <span class="birth-date">生年月日：未入力または不正</span>
            @endif
          </div>

          <div class="gender-block">
              <span class="gender-label">性別</span>
              @php
                $genderMap = ['male' => '男性', 'female' => '女性', 'other' => 'その他'];
                $genderVal = $resume->gender ? ($genderMap[$resume->gender] ?? $resume->gender) : '—';
              @endphp
              <span class="gender-value">{{ $genderVal }}</span>
          </div>
        </div>

        <div class="address-section">
          <div class="address-section-wrapper">
            <div class="address-furigana">
              <div class="address-label">ふりがな</div>
              <div class="address-value">{{ '' }}</div>
            </div>
            <div class="address-detail">
              <div class="address-label">現住所　〒</div>
              <div class="address-value">{{ $resume->address }}</div>
            </div>
          </div>
          <div class="phone-label">{{ $resume->phone }}</div>
        </div>

        <div class="contact-address-section">
          <div class="contact-address-section-wrapper">
            <div class="contact-address-furigana">
              <div class="contact-address-label">ふりがな</div>
              <div class="contact-address-value">{{ '' }}</div>
            </div>
            <div class="contact-address-detail">
              <div class="contact-address-label">連絡先　〒</div>
              <div class="contact-address-value">{{ $resume->contact_address }}</div>
            </div>
          </div>
          <div class="phone-label">{{ $resume->contact_phone ?? '' }}</div>
        </div>

        <div class="email-label">メールアドレス</div>
        <div class="email-value">{{ $resume->email }}</div>

        <table class="history-table">
          <tr>
            <th class="year-header">年</th>
            <th class="month-header">月</th>
            <th class="history-header">学　歴・職　歴</th>
          </tr>
          @php
            // 全ての履歴（学歴＋職歴）
            $educations = $resume->histories->where('type', 'education')->sortBy('sort_order')->values();
            $works = $resume->histories->where('type', 'work')->sortBy('sort_order')->values();
            
            $processedHistories = collect();
            if ($educations->count() > 0) {
                $firstDesc = str_replace([' ', '　'], '', $educations->first()->description ?? '');
                if ($firstDesc !== '学歴') {
                    $processedHistories->push((object)['year' => '', 'month' => '', 'description' => '学　歴', 'is_header' => true]);
                } else {
                    $educations->first()->is_header = true;
                }
                foreach ($educations as $edu) {
                    $processedHistories->push($edu);
                }
            }
            if ($works->count() > 0) {
                $firstDesc = str_replace([' ', '　'], '', $works->first()->description ?? '');
                if ($firstDesc !== '職歴') {
                    $processedHistories->push((object)['year' => '', 'month' => '', 'description' => '職　歴', 'is_header' => true]);
                } else {
                    $works->first()->is_header = true;
                }
                foreach ($works as $work) {
                    $processedHistories->push($work);
                }
            }
            $all = $processedHistories;

            // 最後に実データがあるインデックスを探す
            $lastFilled = null;
            foreach ($all as $k => $e) {
                $combined = trim(((string)($e->year ?? '')) . ((string)($e->month ?? '')) . ' ' . ((string)($e->description ?? '')));
                if ($combined !== '') {
                    $lastFilled = $k;
                }
            }
            // マーカーを置く位置：通常は最後の入力の次の行、ただし最後の行が埋まっている場合は同じ行に付与
            $markerIndex = null;
            if (!is_null($lastFilled)) {
                $markerIndex = ($lastFilled < 14) ? $lastFilled + 1 : $lastFilled;
            }
          @endphp
          @for ($i = 0; $i < 15; $i++)
            @php $item = $all->get($i); @endphp
            <tr>
              <td>
                <div class="{{ trim((string)($item->year ?? '')) !== '' ? 'cell-content filled' : 'cell-content' }}">{{ $item->year ?? '' }}</div>
              </td>
              <td>
                <div class="{{ trim((string)($item->month ?? '')) !== '' ? 'cell-content filled' : 'cell-content' }}">{{ $item->month ?? '' }}</div>
              </td>
              <td>
                @php
                  $descRaw = $item->description ?? '';
                  // strip accidental type prefixes like "education:" or "work:"
                  $desc = preg_replace('/^(education|work):\s*/i', '', (string)$descRaw);
                @endphp
                <div class="{{ trim((string)$desc) !== '' ? 'cell-content filled' : 'cell-content' }} {{ isset($item->is_header) && $item->is_header ? 'history-section-title' : '' }}">
                  @if(!is_null($markerIndex) && $i === $markerIndex)
                    {{ $desc }}@if($desc !== '')　@endif<span class="marker">以上</span>
                  @else
                    {{ $desc }}
                  @endif
                </div>
              </td>
            </tr>
          @endfor
        </table>
      </div>

      <div class="page page-right">
        <table class="history-table-right">
          <tr>
            <th class="year-header">年</th>
            <th class="month-header">月</th>
            <th class="history-header">学　歴・職　歴</th>
          </tr>
          @php
            // 右カラムには左側に表示した残りの履歴を表示（左で15行使用）
            $all = isset($processedHistories) ? $processedHistories : $resume->histories->sortBy('sort_order')->values();
          @endphp
          @for ($i = 15; $i < 22; $i++)
            @php $item = $all->get($i); @endphp
            <tr>
              <td>
                <div class="{{ trim((string)($item->year ?? '')) !== '' ? 'cell-content filled' : 'cell-content' }}">{{ $item->year ?? '' }}</div>
              </td>
              <td>
                <div class="{{ trim((string)($item->month ?? '')) !== '' ? 'cell-content filled' : 'cell-content' }}">{{ $item->month ?? '' }}</div>
              </td>
              <td>
                @php $descRawR = $item->description ?? ''; $descR = preg_replace('/^(education|work):\s*/i', '', (string)$descRawR); @endphp
                <div class="{{ trim((string)$descR) !== '' ? 'cell-content filled' : 'cell-content' }} {{ isset($item->is_header) && $item->is_header ? 'history-section-title' : '' }}">{{ $descR }}</div>
              </td>
            </tr>
          @endfor
        </table>

        <table class="license-table">
          <tr>
            <th class="year-header">年</th>
            <th class="month-header">月</th>
            <th class="license-header">免　許・資　格</th>
          </tr>
          @php
            // 免許・資格を常に6行表示
            $licenses = $resume->licenses->values();
          @endphp
          @for ($j = 0; $j < 6; $j++)
            @php $lic = $licenses->get($j); @endphp
            <tr>
              <td>
                <div class="{{ trim((string)($lic->year ?? '')) !== '' ? 'cell-content filled' : 'cell-content' }}">{{ $lic->year ?? '' }}</div>
              </td>
              <td>
                <div class="{{ trim((string)($lic->month ?? '')) !== '' ? 'cell-content filled' : 'cell-content' }}">{{ $lic->month ?? '' }}</div>
              </td>
              <td>
                <div class="{{ trim((string)trim(($lic->name ?? '') . ' ' . ($lic->details ?? ''))) !== '' ? 'cell-content filled' : 'cell-content' }}">{{ trim(($lic->name ?? '') . ' ' . ($lic->details ?? '')) }}</div>
              </td>
            </tr>
          @endfor
        </table>

        <div class="motivation-section">
          <div class="section-header">志望の動機、特技、好きな学科、アピールポイントなど</div>
          <div class="content-area">{{ $resume->profile->motivation ?? '' }}</div>
        </div>

        <div class="request-section">
          <div class="request-box">
            <div class="section-header">本人希望欄</div>
            <div class="request-area">
              <div class="request-row">{{ $resume->profile->personal_requests ?? '' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>

<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>履歴書作成</title>
  <!-- Bootstrap 5 CDN: simple, responsive form UI for better UX -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <!-- form-specific CSS (overrides) -->
    <link rel="stylesheet" href="{{ asset('form.css') }}">
  </head>
  <body>
    <div class="container py-4">
  <form method="post" class="resume-form" action="{{ route('resumes.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h3 class="card-title mb-0">履歴書作成</h3>
                <small class="text-muted">作成時の日付は自動で保存されます</small>
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-primary">保存</button>
              </div>
            </div>

            @if(session('public_link'))
              <div class="mb-3">
                <div class="alert alert-success">
                  履歴書を保存しました。下記のリンクから保存した履歴書を確認できます（共有は慎重にお願いします）。
                  <div class="mt-2"><a href="{{ session('public_link') }}" target="_blank">{{ session('public_link') }}</a></div>
                </div>
              </div>
            @endif

            <div class="row">
              <div class="col-md-8">
                <div class="form-section">
                  <label class="form-label required">氏名</label>
                  <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="例: 山田 太郎">
                </div>

                <div class="form-section">
                  <label class="form-label">ふりがな</label>
                  <input type="text" name="furigana" class="form-control" value="{{ old('furigana') }}" placeholder="例: やまだ たろう">
                </div>

                <div class="row">
                  <div class="col-md-6 form-section">
                    <label class="form-label">生年月日</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}">
                  </div>
                  <div class="col-md-6 form-section">
                    <label class="form-label">性別</label>
                    <select name="gender" class="form-select">
                      <option value="">選択してください</option>
                      <option value="male" @if(old('gender')=='male') selected @endif>男性</option>
                      <option value="female" @if(old('gender')=='female') selected @endif>女性</option>
                      <option value="other" @if(old('gender')=='other') selected @endif>その他</option>
                    </select>
                  </div>
                </div>

                <div class="form-section">
                  <label class="form-label">現住所</label>
                  <div class="mb-2 d-flex align-items-center" style="gap:.5rem;">
                    @php
                      $addrPostal = old('address_postal', '');
                      $addrPart1 = '';
                      $addrPart2 = '';
                      if ($addrPostal) {
                          // normalize: remove non-digits then split
                          $d = preg_replace('/[^0-9]/', '', $addrPostal);
                          if (strlen($d) >= 7) {
                              $addrPart1 = substr($d,0,3);
                              $addrPart2 = substr($d,3,4);
                          } elseif (strlen($d) > 3) {
                              $addrPart1 = substr($d,0,3);
                              $addrPart2 = substr($d,3);
                          } else {
                              $addrPart1 = $d;
                          }
                      }
                    @endphp
                    <input type="text" name="address_postal_part1" id="address_postal_part1" class="form-control postal-part" value="{{ $addrPart1 }}" placeholder="000" maxlength="3" inputmode="numeric" pattern="\d{3}">
                    <span class="text-muted">-</span>
                    <input type="text" name="address_postal_part2" id="address_postal_part2" class="form-control postal-part" value="{{ $addrPart2 }}" placeholder="0000" maxlength="4" inputmode="numeric" pattern="\d{4}">
                    <input type="hidden" name="address_postal" id="address_postal" value="{{ old('address_postal') }}">
                  </div>
                  <textarea name="address" class="form-control" rows="2" placeholder="都道府県・市区町村・番地など">{{ old('address') }}</textarea>
                </div>

                <div class="row">
                  <div class="col-md-6 form-section">
                    <label class="form-label">電話番号</label>
                      @php
                        $phone = old('phone', '');
                        $p1 = '';
                        $p2 = '';
                        $p3 = '';
                        if ($phone) {
                            $d = preg_replace('/[^0-9]/', '', $phone);
                            if (strlen($d) >= 11) {
                                $p1 = substr($d,0,3);
                                $p2 = substr($d,3,4);
                                $p3 = substr($d,7,4);
                            } elseif (strlen($d) > 7) {
                                $p1 = substr($d,0,3);
                                $p2 = substr($d,3,4);
                                $p3 = substr($d,7);
                            } elseif (strlen($d) > 3) {
                                $p1 = substr($d,0,3);
                                $p2 = substr($d,3);
                            } else {
                                $p1 = $d;
                            }
                        }
                      @endphp
                      <div class="mb-2 d-flex align-items-center" style="gap:.5rem;">
                        <input type="text" name="phone_part1" id="phone_part1" class="form-control phone-part" value="{{ $p1 }}" placeholder="000" inputmode="numeric">
                        <span class="text-muted">-</span>
                        <input type="text" name="phone_part2" id="phone_part2" class="form-control phone-part" value="{{ $p2 }}" placeholder="0000" inputmode="numeric">
                        <span class="text-muted">-</span>
                        <input type="text" name="phone_part3" id="phone_part3" class="form-control phone-part" value="{{ $p3 }}" placeholder="0000" inputmode="numeric">
                        <input type="hidden" name="phone" id="phone" value="{{ old('phone') }}">
                      </div>
                  </div>
                  <div class="col-md-6 form-section">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="example@example.com">
                  </div>
                </div>

                <hr>

                <div class="form-section">
                  <label class="form-label">学歴（教育）</label>
                  @php
                    $oldHistories = old('histories', []);
                    $eduRows = [];
                    foreach ($oldHistories as $h) {
                      if (($h['type'] ?? 'education') === 'education') $eduRows[] = $h;
                    }
                    $eduCount = max(4, count($eduRows));
                  @endphp
                  <div id="educationRows">
                    @for ($i = 0; $i < $eduCount; $i++)
                      @php $row = $eduRows[$i] ?? ['year'=>old('histories.'.$i.'.year', ''),'month'=>old('histories.'.$i.'.month',''),'description'=>old('histories.'.$i.'.description','')]; @endphp
                      <div class="input-group mb-2 education-row">
                        @php
                          // convert existing gregorian year to era + eraYear if present
                          $era = '';
                          $eraYear = '';
                          if (!empty($row['year'])) {
                              $gy = intval($row['year']);
                              if ($gy >= 2019) { $era = '令和'; $eraYear = $gy - 2018; }
                              elseif ($gy >= 1989) { $era = '平成'; $eraYear = $gy - 1988; }
                              else { $era = '昭和'; $eraYear = $gy - 1925; }
                          }
                        @endphp
                        <select class="form-select era-select" data-type="era">
                          <option value="">元号</option>
                          <option value="昭和" @if($era=='昭和') selected @endif>昭和</option>
                          <option value="平成" @if($era=='平成') selected @endif>平成</option>
                          <option value="令和" @if($era=='令和') selected @endif>令和</option>
                        </select>
                        <select class="form-select era-year-select" data-type="era_year">
                          <option value="">年</option>
                          @for($ey = 1; $ey <= 99; $ey++)
                            <option value="{{ $ey }}" @if($eraYear == $ey) selected @endif>{{ $ey }}</option>
                          @endfor
                        </select>
                        <select class="form-select" data-type="month">
                          <option value="">月</option>
                          @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @if(($row['month'] ?? '') == $m) selected @endif>{{ $m }}</option>
                          @endfor
                        </select>
                        <input type="text" class="form-control" placeholder="学校名・学部・備考" value="{{ $row['description'] ?? '' }}" data-type="description">
                        <button type="button" class="btn btn-outline-secondary btn-remove-education">−</button>
                      </div>
                    @endfor
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" id="addEducation" class="btn btn-sm btn-outline-primary">学歴を追加</button>
                    <div class="form-text">1行につき1件入力してください（年・月は任意）。</div>
                  </div>
                </div>

                <div class="form-section">
                  <label class="form-label">職歴（work）</label>
                  @php
                    $workRows = [];
                    foreach ($oldHistories as $h) {
                      if (($h['type'] ?? 'education') === 'work') $workRows[] = $h;
                    }
                    $workCount = max(3, count($workRows));
                  @endphp
                  <div id="workRows">
                    @for ($i = 0; $i < $workCount; $i++)
                      @php $row = $workRows[$i] ?? ['year'=>'','month'=>'','description'=>'']; @endphp
                      <div class="input-group mb-2 work-row">
                        @php
                          $era = '';
                          $eraYear = '';
                          if (!empty($row['year'])) {
                              $gy = intval($row['year']);
                              if ($gy >= 2019) { $era = '令和'; $eraYear = $gy - 2018; }
                              elseif ($gy >= 1989) { $era = '平成'; $eraYear = $gy - 1988; }
                              else { $era = '昭和'; $eraYear = $gy - 1925; }
                          }
                        @endphp
                        <select class="form-select era-select" data-type="era">
                          <option value="">元号</option>
                          <option value="昭和" @if($era=='昭和') selected @endif>昭和</option>
                          <option value="平成" @if($era=='平成') selected @endif>平成</option>
                          <option value="令和" @if($era=='令和') selected @endif>令和</option>
                        </select>
                        <select class="form-select era-year-select" data-type="era_year">
                          <option value="">年</option>
                          @for($ey = 1; $ey <= 99; $ey++)
                            <option value="{{ $ey }}" @if($eraYear == $ey) selected @endif>{{ $ey }}</option>
                          @endfor
                        </select>
                        <select class="form-select" data-type="month">
                          <option value="">月</option>
                          @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @if(($row['month'] ?? '') == $m) selected @endif>{{ $m }}</option>
                          @endfor
                        </select>
                        <input type="text" class="form-control" placeholder="会社名・役職・備考" value="{{ $row['description'] ?? '' }}" data-type="description">
                        <button type="button" class="btn btn-outline-secondary btn-remove-work">−</button>
                      </div>
                    @endfor
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" id="addWork" class="btn btn-sm btn-outline-primary">職歴を追加</button>
                    <div class="form-text">職歴は最新順で入力してください（年・月は任意）。</div>
                  </div>
                </div>

                <div class="form-section">
                  <label class="form-label">免許・資格</label>
                  @php $oldLicenses = old('licenses', []); $licCount = max(3, count($oldLicenses)); @endphp
                  <div id="licenseRows">
                    @for ($i = 0; $i < $licCount; $i++)
                      @php $l = $oldLicenses[$i] ?? ['year'=>'','month'=>'','name'=>'','details'=>'']; @endphp
                      <div class="input-group mb-2 license-row">
                        @php
                          $eraL = '';
                          $eraYearL = '';
                          if (!empty($l['year'])) {
                              $gy = intval($l['year']);
                              if ($gy >= 2019) { $eraL = '令和'; $eraYearL = $gy - 2018; }
                              elseif ($gy >= 1989) { $eraL = '平成'; $eraYearL = $gy - 1988; }
                              else { $eraL = '昭和'; $eraYearL = $gy - 1925; }
                          }
                        @endphp
                        <select class="form-select era-select" data-type="era">
                          <option value="">元号</option>
                          <option value="昭和" @if($eraL=='昭和') selected @endif>昭和</option>
                          <option value="平成" @if($eraL=='平成') selected @endif>平成</option>
                          <option value="令和" @if($eraL=='令和') selected @endif>令和</option>
                        </select>
                        <select class="form-select era-year-select" data-type="era_year">
                          <option value="">年</option>
                          @for($ey = 1; $ey <= 99; $ey++)
                            <option value="{{ $ey }}" @if($eraYearL == $ey) selected @endif>{{ $ey }}</option>
                          @endfor
                        </select>
                        <select class="form-select" data-type="month">
                          <option value="">月</option>
                          @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @if(($l['month'] ?? '') == $m) selected @endif>{{ $m }}</option>
                          @endfor
                        </select>
                        <input type="text" class="form-control" placeholder="名称" value="{{ $l['name'] ?? '' }}" data-type="name">
                        <input type="text" class="form-control" placeholder="備考" value="{{ $l['details'] ?? '' }}" data-type="details">
                        <button type="button" class="btn btn-outline-secondary btn-remove-license">−</button>
                      </div>
                    @endfor
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" id="addLicense" class="btn btn-sm btn-outline-primary">免許・資格を追加</button>
                    <div class="form-text">行を追加して項目ごとに入力してください。</div>
                  </div>
                </div>

                <div class="form-section">
                  <label class="form-label">志望の動機・自己PR</label>
                  <textarea name="motivation" class="form-control" rows="4">{{ old('motivation') }}</textarea>
                </div>

                <div class="form-section">
                  <label class="form-label">本人希望欄</label>
                  <textarea name="personal_requests" class="form-control" rows="3">{{ old('personal_requests') }}</textarea>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-section text-center">
                  <label class="form-label">写真（任意）</label>
                  <div class="mb-2">
                    <img id="photoPreview" src="" alt="写真プレビュー" class="photo-preview d-none" />
                  </div>
                  <input type="file" name="photo" accept="image/*" class="form-control" id="photoInput">
                  <div class="form-text">推奨サイズ: 縦140×横100 など証明写真に近い比率</div>
                </div>

                <div class="form-section">
                  <label class="form-label">連絡先（現住所と異なる場合）</label>
                  <div class="mb-2 d-flex align-items-center" style="gap:.5rem;">
                    @php
                      $contactPostal = old('contact_postal', '');
                      $contactPart1 = '';
                      $contactPart2 = '';
                      if ($contactPostal) {
                          $d = preg_replace('/[^0-9]/', '', $contactPostal);
                          if (strlen($d) >= 7) {
                              $contactPart1 = substr($d,0,3);
                              $contactPart2 = substr($d,3,4);
                          } elseif (strlen($d) > 3) {
                              $contactPart1 = substr($d,0,3);
                              $contactPart2 = substr($d,3);
                          } else {
                              $contactPart1 = $d;
                          }
                      }
                    @endphp
                    <input type="text" name="contact_postal_part1" id="contact_postal_part1" class="form-control postal-part" value="{{ $contactPart1 }}" placeholder="000" maxlength="3" inputmode="numeric" pattern="\d{3}">
                    <span class="text-muted">-</span>
                    <input type="text" name="contact_postal_part2" id="contact_postal_part2" class="form-control postal-part" value="{{ $contactPart2 }}" placeholder="0000" maxlength="4" inputmode="numeric" pattern="\d{4}">
                    <input type="hidden" name="contact_postal" id="contact_postal" value="{{ old('contact_postal') }}">
                  </div>
                  <textarea name="contact_address" class="form-control" rows="3">{{ old('contact_address') }}</textarea>
                </div>

                <div class="form-section">
                  <label class="form-label">連絡先電話</label>
                  @php
                    $cphone = old('contact_phone', '');
                    $cp1 = '';
                    $cp2 = '';
                    $cp3 = '';
                    if ($cphone) {
                        $d = preg_replace('/[^0-9]/', '', $cphone);
                        if (strlen($d) >= 11) {
                            $cp1 = substr($d,0,3);
                            $cp2 = substr($d,3,4);
                            $cp3 = substr($d,7,4);
                        } elseif (strlen($d) > 7) {
                            $cp1 = substr($d,0,3);
                            $cp2 = substr($d,3,4);
                            $cp3 = substr($d,7);
                        } elseif (strlen($d) > 3) {
                            $cp1 = substr($d,0,3);
                            $cp2 = substr($d,3);
                        } else {
                            $cp1 = $d;
                        }
                    }
                  @endphp
                  <div class="mb-2 d-flex align-items-center" style="gap:.5rem;">
                    <input type="text" name="contact_phone_part1" id="contact_phone_part1" class="form-control phone-part" value="{{ $cp1 }}" placeholder="000" inputmode="numeric">
                    <span class="text-muted">-</span>
                    <input type="text" name="contact_phone_part2" id="contact_phone_part2" class="form-control phone-part" value="{{ $cp2 }}" placeholder="0000" inputmode="numeric">
                    <span class="text-muted">-</span>
                    <input type="text" name="contact_phone_part3" id="contact_phone_part3" class="form-control phone-part" value="{{ $cp3 }}" placeholder="0000" inputmode="numeric">
                    <input type="hidden" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <script>
      // postal code helpers: sync split parts into hidden combined field, auto-advance, restrict to digits
      function joinPostal(part1El, part2El, hiddenEl) {
        const p1 = (part1El.value || '').replace(/[^0-9]/g, '');
        const p2 = (part2El.value || '').replace(/[^0-9]/g, '');
        if (p1.length === 0 && p2.length === 0) {
          hiddenEl.value = '';
          return;
        }
        let combined = p1;
        if (p2.length > 0) combined += '-' + p2;
        hiddenEl.value = combined;
      }

      function setupPostalPair(part1Id, part2Id, hiddenId) {
        const p1 = document.getElementById(part1Id);
        const p2 = document.getElementById(part2Id);
        const hidden = document.getElementById(hiddenId);
        if (!p1 || !p2 || !hidden) return;
        // ensure only digits are allowed
        [p1, p2].forEach(el => {
          el.addEventListener('input', (e) => {
            const cleaned = el.value.replace(/[^0-9]/g, '');
            if (el.value !== cleaned) el.value = cleaned;
            // auto-advance
            if (el === p1 && cleaned.length >= 3) {
              p1.value = cleaned.slice(0,3);
              p2.focus();
            }
            // limit lengths enforced by maxlength attribute
            joinPostal(p1, p2, hidden);
          });
        });
        // initialize combined on load
        joinPostal(p1, p2, hidden);
      }

      // phone helpers: join 3 parts into hidden (e.g. 090-1234-5678), auto-advance and restrict digits
      function joinPhone(p1El, p2El, p3El, hiddenEl) {
        const a = (p1El.value || '').replace(/[^0-9]/g, '');
        const b = (p2El.value || '').replace(/[^0-9]/g, '');
        const c = (p3El.value || '').replace(/[^0-9]/g, '');
        if (!a && !b && !c) { hiddenEl.value = ''; return; }
        let combined = a;
        if (b) combined += '-' + b;
        if (c) combined += '-' + c;
        hiddenEl.value = combined;
      }

      function setupPhoneTriplet(p1Id, p2Id, p3Id, hiddenId) {
        const p1 = document.getElementById(p1Id);
        const p2 = document.getElementById(p2Id);
        const p3 = document.getElementById(p3Id);
        const hidden = document.getElementById(hiddenId);
        if (!p1 || !p2 || !p3 || !hidden) return;
        [p1, p2, p3].forEach(el => {
          el.addEventListener('input', () => {
            const cleaned = el.value.replace(/[^0-9]/g, '');
            if (el.value !== cleaned) el.value = cleaned;
            // do not auto-advance or truncate — regional phone lengths vary
            joinPhone(p1, p2, p3, hidden);
          });
        });
        // initialize
        joinPhone(p1, p2, p3, hidden);
      }
      // photo preview
      document.getElementById('photoInput')?.addEventListener('change', function (e) {
        const file = e.target.files && e.target.files[0];
        const img = document.getElementById('photoPreview');
        if (!file) { img.src = ''; img.classList.add('d-none'); return; }
        const url = URL.createObjectURL(file);
        img.src = url; img.classList.remove('d-none');
      });
      // dynamic rows handling for education, work and license
      function reindexRows(container, prefix, typeValue) {
        const rows = Array.from(container.querySelectorAll(':scope > div'));
        rows.forEach((row, idx) => {
          // create/ensure inputs with proper names
          const inputs = row.querySelectorAll('[data-type]');
          inputs.forEach(input => {
            const t = input.getAttribute('data-type');
            input.name = prefix + '[' + idx + '][' + t + ']';
          });
          // compute gregorian year from era+era_year if present, otherwise use a year input if exists
          const eraEl = row.querySelector('[data-type="era"]');
          const eraYearEl = row.querySelector('[data-type="era_year"]');
          const directYearEl = row.querySelector('[data-type="year"]');
          let gregYear = '';
          if (eraEl && eraYearEl) {
            const era = eraEl.value;
            const ey = parseInt(eraYearEl.value, 10);
            if (era && !isNaN(ey)) {
              if (era === '令和') gregYear = 2018 + ey;
              else if (era === '平成') gregYear = 1988 + ey;
              else if (era === '昭和') gregYear = 1925 + ey;
            }
          } else if (directYearEl) {
            gregYear = directYearEl.value || '';
          }
          // ensure hidden year input exists and set value
          let yearHidden = row.querySelector('input[name="' + prefix + '[' + idx + '][year]"]');
          if (!yearHidden) {
            yearHidden = document.createElement('input');
            yearHidden.type = 'hidden';
            yearHidden.name = prefix + '[' + idx + '][year]';
            row.appendChild(yearHidden);
          }
          yearHidden.value = gregYear;
          // ensure type hidden input exists for histories
          if (typeValue) {
            let typeInput = row.querySelector('input[name="' + prefix + '[' + idx + '][type]"]');
            if (!typeInput) {
              typeInput = document.createElement('input');
              typeInput.type = 'hidden';
              typeInput.name = prefix + '[' + idx + '][type]';
              typeInput.value = typeValue;
              row.appendChild(typeInput);
            } else {
              typeInput.value = typeValue;
            }
          }
          // attach change handlers to era/era_year to recompute hidden year when user changes
          if (eraEl && eraYearEl) {
            if (!eraEl._hasHandler) {
              eraEl.addEventListener('change', () => {
                const e = eraEl.value; const ey = parseInt(eraYearEl.value, 10);
                let g = '';
                if (e && !isNaN(ey)) {
                  if (e === '令和') g = 2018 + ey;
                  else if (e === '平成') g = 1988 + ey;
                  else if (e === '昭和') g = 1925 + ey;
                }
                yearHidden.value = g;
              });
              eraEl._hasHandler = true;
            }
            if (!eraYearEl._hasHandler) {
              eraYearEl.addEventListener('change', () => {
                const e = eraEl.value; const ey = parseInt(eraYearEl.value, 10);
                let g = '';
                if (e && !isNaN(ey)) {
                  if (e === '令和') g = 2018 + ey;
                  else if (e === '平成') g = 1988 + ey;
                  else if (e === '昭和') g = 1925 + ey;
                }
                yearHidden.value = g;
              });
              eraYearEl._hasHandler = true;
            }
          }
        });
      }

      function wireAddRemove(containerId, addBtnId, rowClass, prefix, typeValue) {
        const container = document.getElementById(containerId);
        document.getElementById(addBtnId).addEventListener('click', () => {
          const newRow = document.createElement('div');
          newRow.className = rowClass + ' input-group mb-2';
          // create inputs by copying from first existing row if exists
          const template = container.querySelector(':scope > div');
          if (template) {
            newRow.innerHTML = template.innerHTML;
            container.appendChild(newRow);
          } else {
            // fallback: create era select + era-year + month + text input
            let eraOptions = '<option value="">元号</option><option value="昭和">昭和</option><option value="平成">平成</option><option value="令和">令和</option>';
            let eraYearOptions = '<option value="">年</option>';
            for (let ey = 1; ey <= 99; ey++) { eraYearOptions += `<option value="${ey}">${ey}</option>`; }
            let monthOptions = '<option value="">月</option>';
            for (let m = 1; m <= 12; m++) { monthOptions += `<option value="${m}">${m}</option>`; }
            newRow.innerHTML = `<select class="form-select era-select" data-type="era">${eraOptions}</select>` +
              `<select class="form-select era-year-select" data-type="era_year">${eraYearOptions}</select>` +
              `<select class="form-select" data-type="month">${monthOptions}</select>` +
              `<input class="form-control" data-type="description" placeholder="内容">` +
              `<button type="button" class="btn btn-outline-secondary btn-remove">−</button>`;
            container.appendChild(newRow);
          }
          reindexRows(container, prefix, typeValue);
          attachRemoveHandlers(container);
        });
        attachRemoveHandlers(container);
        reindexRows(container, prefix, typeValue);
      }

      function attachRemoveHandlers(container) {
        container.querySelectorAll('.btn-remove-education, .btn-remove-work, .btn-remove-license, .btn-remove').forEach(btn => {
          if (btn._hasHandler) return; // avoid double-binding
          btn.addEventListener('click', (e) => {
            const row = e.target.closest('div');
            if (!row) return;
            row.remove();
            // after removal, reindex for each container
            reindexRows(document.getElementById('educationRows'), 'histories', 'education');
            reindexRows(document.getElementById('workRows'), 'histories', 'work');
            reindexRows(document.getElementById('licenseRows'), 'licenses', null);
          });
          btn._hasHandler = true;
        });
      }

      // initialize wiring
      document.addEventListener('DOMContentLoaded', function () {
        wireAddRemove('educationRows', 'addEducation', 'education-row', 'histories', 'education');
        wireAddRemove('workRows', 'addWork', 'work-row', 'histories', 'work');
        wireAddRemove('licenseRows', 'addLicense', 'license-row', 'licenses', null);
        // setup postal pairs
        setupPostalPair('address_postal_part1', 'address_postal_part2', 'address_postal');
        setupPostalPair('contact_postal_part1', 'contact_postal_part2', 'contact_postal');
        // setup phone triplets
        setupPhoneTriplet('phone_part1', 'phone_part2', 'phone_part3', 'phone');
        setupPhoneTriplet('contact_phone_part1', 'contact_phone_part2', 'contact_phone_part3', 'contact_phone');
        // ensure combined values are set before submit
        const form = document.querySelector('form');
        form?.addEventListener('submit', function () {
          const a1 = document.getElementById('address_postal_part1');
          const a2 = document.getElementById('address_postal_part2');
          const ah = document.getElementById('address_postal');
          if (a1 && a2 && ah) joinPostal(a1, a2, ah);
          const c1 = document.getElementById('contact_postal_part1');
          const c2 = document.getElementById('contact_postal_part2');
          const ch = document.getElementById('contact_postal');
          if (c1 && c2 && ch) joinPostal(c1, c2, ch);
          // phones
          const p1 = document.getElementById('phone_part1');
          const p2 = document.getElementById('phone_part2');
          const p3 = document.getElementById('phone_part3');
          const ph = document.getElementById('phone');
          if (p1 && p2 && p3 && ph) joinPhone(p1, p2, p3, ph);
          const cp1 = document.getElementById('contact_phone_part1');
          const cp2 = document.getElementById('contact_phone_part2');
          const cp3 = document.getElementById('contact_phone_part3');
          const cph = document.getElementById('contact_phone');
          if (cp1 && cp2 && cp3 && cph) joinPhone(cp1, cp2, cp3, cph);
        });
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  </body>
</html>

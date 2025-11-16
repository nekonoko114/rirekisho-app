<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>履歴書編集</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <!-- form-specific CSS (overrides) -->
  <link href="{{ asset('form.css') }}" rel="stylesheet">
  </head>
  <body>
    <div class="container py-4">
  <form method="post" class="resume-form" action="{{ route('resumes.update', $resume) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h3 class="card-title mb-0">履歴書編集</h3>
                <small class="text-muted">ID: {{ $resume->id }}</small>
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-primary">更新</button>
              </div>
            </div>

            {{-- For brevity reuse the same markup as create but prefill with $resume values when old() empty. --}}
            {{-- Name, furigana, birth_date, gender, address, phone, email, histories, licenses, profile --}}
            <div class="row">
              <div class="col-md-8">
                <div class="form-section">
                  <label class="form-label required">氏名</label>
                  <input type="text" name="name" class="form-control" value="{{ old('name', $resume->name) }}">
                </div>
                <div class="form-section">
                  <label class="form-label">ふりがな</label>
                  <input type="text" name="furigana" class="form-control" value="{{ old('furigana', $resume->furigana) }}">
                </div>
                <div class="row">
                  <div class="col-md-6 form-section">
                    <label class="form-label">生年月日</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', optional($resume->birth_date)->format('Y-m-d')) }}">
                  </div>
                  <div class="col-md-6 form-section">
                    <label class="form-label">性別</label>
                    <select name="gender" class="form-select">
                      <option value="">選択してください</option>
                      <option value="male" @if(old('gender', $resume->gender)=='male') selected @endif>男性</option>
                      <option value="female" @if(old('gender', $resume->gender)=='female') selected @endif>女性</option>
                      <option value="other" @if(old('gender', $resume->gender)=='other') selected @endif>その他</option>
                    </select>
                  </div>
                </div>

                {{-- Address and contact inputs simplified for edit: reuse hidden postal parts if present --}}
                <div class="form-section">
                  <label class="form-label">現住所</label>
                  <div class="mb-2 d-flex align-items-center" style="gap:.5rem;">
                    @php
                      $addr = old('address', $resume->address);
                      $postal = old('address_postal', $resume->address_postal);
                      $p1 = $p2 = '';
                      if ($postal) {
                        $d = preg_replace('/[^0-9]/','',$postal);
                        $p1 = substr($d,0,3);
                        $p2 = substr($d,3);
                      }
                    @endphp
                    <input type="text" name="address_postal_part1" class="form-control postal-part" value="{{ $p1 }}" maxlength="3">
                    <span class="text-muted">-</span>
                    <input type="text" name="address_postal_part2" class="form-control postal-part" value="{{ $p2 }}" maxlength="4">
                    <input type="hidden" name="address_postal" value="{{ old('address_postal', $resume->address_postal) }}">
                  </div>
                  <textarea name="address" class="form-control" rows="2">{{ $addr }}</textarea>
                </div>

                {{-- Phone/email --}}
                <div class="row">
                  <div class="col-md-6 form-section">
                    <label class="form-label">電話番号</label>
                    @php
                      $phone = old('phone', $resume->phone);
                      $a=$b=$c='';
                      if ($phone) {
                        $d = preg_replace('/[^0-9]/','',$phone);
                        if (strlen($d) >= 11) { $a=substr($d,0,3); $b=substr($d,3,4); $c=substr($d,7,4); }
                        elseif (strlen($d)>7) { $a=substr($d,0,3); $b=substr($d,3,4); $c=substr($d,7); }
                        elseif (strlen($d)>3) { $a=substr($d,0,3); $b=substr($d,3); }
                        else { $a=$d; }
                      }
                    @endphp
                    <div class="mb-2 d-flex align-items-center" style="gap:.5rem;">
                      <input type="text" name="phone_part1" class="form-control" value="{{ $a }}">
                      <span class="text-muted">-</span>
                      <input type="text" name="phone_part2" class="form-control" value="{{ $b }}">
                      <span class="text-muted">-</span>
                      <input type="text" name="phone_part3" class="form-control" value="{{ $c }}">
                      <input type="hidden" name="phone" value="{{ old('phone', $resume->phone) }}">
                    </div>
                  </div>
                  <div class="col-md-6 form-section">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $resume->email) }}">
                  </div>
                </div>

                <div class="form-text text-muted">職歴・学歴・免許の編集は行を追加・削除して更新できます。</div>

                <div class="form-section">
                  <label class="form-label">学歴（教育）</label>
                  @php
                    $eduRows = $resume->histories->where('type','education')->sortBy('sort_order')->values();
                    $eduCount = max(4, $eduRows->count());
                  @endphp
                  <div id="educationRows">
                    @for ($i = 0; $i < $eduCount; $i++)
                      @php $row = $eduRows->get($i);
                        $year = $row->year ?? '';
                        $month = $row->month ?? '';
                        $desc = $row->description ?? '';
                        // convert gregorian year to era/year if possible
                        $era = '';
                        $eraYear = '';
                        if (!empty($year)) {
                            $gy = intval($year);
                            if ($gy >= 2019) { $era = '令和'; $eraYear = $gy - 2018; }
                            elseif ($gy >= 1989) { $era = '平成'; $eraYear = $gy - 1988; }
                            else { $era = '昭和'; $eraYear = $gy - 1925; }
                        }
                      @endphp
                      <div class="input-group mb-2 education-row">
                        <select name="histories_education[{{ $i }}][era]" class="form-select era-select" data-type="era">
                          <option value="">元号</option>
                          <option value="昭和" @if($era=='昭和') selected @endif>昭和</option>
                          <option value="平成" @if($era=='平成') selected @endif>平成</option>
                          <option value="令和" @if($era=='令和') selected @endif>令和</option>
                        </select>
                        <select name="histories_education[{{ $i }}][era_year]" class="form-select era-year-select" data-type="era_year">
                          <option value="">年</option>
                          @for($ey = 1; $ey <= 99; $ey++)
                            <option value="{{ $ey }}" @if($eraYear == $ey) selected @endif>{{ $ey }}</option>
                          @endfor
                        </select>
                        <select name="histories_education[{{ $i }}][month]" class="form-select" data-type="month">
                          <option value="">月</option>
                          @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @if($month == $m) selected @endif>{{ $m }}</option>
                          @endfor
                        </select>
                        <input type="text" name="histories_education[{{ $i }}][description]" class="form-control" placeholder="学校名・学部・備考" value="{{ $desc }}" data-type="description">
                        <input type="hidden" name="histories_education[{{ $i }}][year]" value="{{ $year }}">
                        <input type="hidden" name="histories_education[{{ $i }}][type]" value="education">
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
                    $workRows = $resume->histories->where('type','work')->sortBy('sort_order')->values();
                    $workCount = max(3, $workRows->count());
                  @endphp
                  <div id="workRows">
                    @for ($i = 0; $i < $workCount; $i++)
                      @php $row = $workRows->get($i);
                        $year = $row->year ?? '';
                        $month = $row->month ?? '';
                        $desc = $row->description ?? '';
                        $era = '';
                        $eraYear = '';
                        if (!empty($year)) {
                            $gy = intval($year);
                            if ($gy >= 2019) { $era = '令和'; $eraYear = $gy - 2018; }
                            elseif ($gy >= 1989) { $era = '平成'; $eraYear = $gy - 1988; }
                            else { $era = '昭和'; $eraYear = $gy - 1925; }
                        }
                      @endphp
                      <div class="input-group mb-2 work-row">
                        <select name="histories_work[{{ $i }}][era]" class="form-select era-select" data-type="era">
                          <option value="">元号</option>
                          <option value="昭和" @if($era=='昭和') selected @endif>昭和</option>
                          <option value="平成" @if($era=='平成') selected @endif>平成</option>
                          <option value="令和" @if($era=='令和') selected @endif>令和</option>
                        </select>
                        <select name="histories_work[{{ $i }}][era_year]" class="form-select era-year-select" data-type="era_year">
                          <option value="">年</option>
                          @for($ey = 1; $ey <= 99; $ey++)
                            <option value="{{ $ey }}" @if($eraYear == $ey) selected @endif>{{ $ey }}</option>
                          @endfor
                        </select>
                        <select name="histories_work[{{ $i }}][month]" class="form-select" data-type="month">
                          <option value="">月</option>
                          @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @if($month == $m) selected @endif>{{ $m }}</option>
                          @endfor
                        </select>
                        <input type="text" name="histories_work[{{ $i }}][description]" class="form-control" placeholder="会社名・役職・備考" value="{{ $desc }}" data-type="description">
                        <input type="hidden" name="histories_work[{{ $i }}][year]" value="{{ $year }}">
                        <input type="hidden" name="histories_work[{{ $i }}][type]" value="work">
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
                  @php
                    $licenses = $resume->licenses->values();
                    $licCount = max(3, $licenses->count());
                  @endphp
                  <div id="licenseRows">
                    @for ($i = 0; $i < $licCount; $i++)
                      @php $l = $licenses->get($i);
                        $year = $l->year ?? '';
                        $month = $l->month ?? '';
                        $name = $l->name ?? '';
                        $details = $l->details ?? '';
                        $eraL=''; $eraYearL='';
                        if (!empty($year)) {
                            $gy = intval($year);
                            if ($gy >= 2019) { $eraL = '令和'; $eraYearL = $gy - 2018; }
                            elseif ($gy >= 1989) { $eraL = '平成'; $eraYearL = $gy - 1988; }
                            else { $eraL = '昭和'; $eraYearL = $gy - 1925; }
                        }
                      @endphp
                      <div class="input-group mb-2 license-row">
                        <select name="licenses[{{ $i }}][era]" class="form-select era-select" data-type="era">
                          <option value="">元号</option>
                          <option value="昭和" @if($eraL=='昭和') selected @endif>昭和</option>
                          <option value="平成" @if($eraL=='平成') selected @endif>平成</option>
                          <option value="令和" @if($eraL=='令和') selected @endif>令和</option>
                        </select>
                        <select name="licenses[{{ $i }}][era_year]" class="form-select era-year-select" data-type="era_year">
                          <option value="">年</option>
                          @for($ey = 1; $ey <= 99; $ey++)
                            <option value="{{ $ey }}" @if($eraYearL == $ey) selected @endif>{{ $ey }}</option>
                          @endfor
                        </select>
                        <select name="licenses[{{ $i }}][month]" class="form-select" data-type="month">
                          <option value="">月</option>
                          @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @if($month == $m) selected @endif>{{ $m }}</option>
                          @endfor
                        </select>
                        <input type="text" name="licenses[{{ $i }}][name]" class="form-control" placeholder="名称" value="{{ $name }}" data-type="name">
                        <input type="text" name="licenses[{{ $i }}][details]" class="form-control" placeholder="備考" value="{{ $details }}" data-type="details">
                        <input type="hidden" name="licenses[{{ $i }}][year]" value="{{ $year }}">
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
                  <textarea name="motivation" class="form-control" rows="4">{{ old('motivation', $resume->profile->motivation ?? '') }}</textarea>
                </div>
                <div class="form-section">
                  <label class="form-label">本人希望欄</label>
                  <textarea name="personal_requests" class="form-control" rows="3">{{ old('personal_requests', $resume->profile->personal_requests ?? '') }}</textarea>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-section text-center">
                  <label class="form-label">写真（任意）</label>
                  <div class="mb-2">
                    @if($resume->photo_path)
                      <img src="{{ asset('storage/'.$resume->photo_path) }}" class="img-fluid" style="max-width:140px;" alt="photo">
                    @endif
                  </div>
                  <input type="file" name="photo" accept="image/*" class="form-control">
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <script>
      // dynamic rows handling (copied/adapted from create view)
      function reindexRows(container, prefix, typeValue) {
        const rows = Array.from(container.querySelectorAll(':scope > div'));
        rows.forEach((row, idx) => {
          const inputs = row.querySelectorAll('[data-type]');
          inputs.forEach(input => {
            const t = input.getAttribute('data-type');
            input.name = prefix + '[' + idx + '][' + t + ']';
          });
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
          let yearHidden = row.querySelector('input[name="' + prefix + '[' + idx + '][year]"]');
          if (!yearHidden) {
            yearHidden = document.createElement('input');
            yearHidden.type = 'hidden';
            yearHidden.name = prefix + '[' + idx + '][year]';
            row.appendChild(yearHidden);
          }
          yearHidden.value = gregYear;
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

      function attachRemoveHandlers(container) {
        container.querySelectorAll('.btn-remove-education, .btn-remove-work, .btn-remove-license, .btn-remove').forEach(btn => {
          if (btn._hasHandler) return;
          btn.addEventListener('click', (e) => {
            const row = e.target.closest('div');
            if (!row) return;
            row.remove();
            reindexRows(document.getElementById('educationRows'), 'histories_education', 'education');
            reindexRows(document.getElementById('workRows'), 'histories_work', 'work');
            reindexRows(document.getElementById('licenseRows'), 'licenses', null);
          });
          btn._hasHandler = true;
        });
      }

      function wireAddRemove(containerId, addBtnId, rowClass, prefix, typeValue) {
        const container = document.getElementById(containerId);
        document.getElementById(addBtnId).addEventListener('click', () => {
          const newRow = document.createElement('div');
          newRow.className = rowClass + ' input-group mb-2';
          const template = container.querySelector(':scope > div');
          if (template) {
            newRow.innerHTML = template.innerHTML;
            container.appendChild(newRow);
          } else {
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

      // postal and phone helpers
      function joinPostal(part1El, part2El, hiddenEl) {
        const p1 = (part1El.value || '').replace(/[^0-9]/g, '');
        const p2 = (part2El.value || '').replace(/[^0-9]/g, '');
        if (p1.length === 0 && p2.length === 0) { hiddenEl.value = ''; return; }
        let combined = p1; if (p2.length > 0) combined += '-' + p2; hiddenEl.value = combined;
      }
      function joinPhone(p1El, p2El, p3El, hiddenEl) {
        const a = (p1El.value || '').replace(/[^0-9]/g, '');
        const b = (p2El.value || '').replace(/[^0-9]/g, '');
        const c = (p3El.value || '').replace(/[^0-9]/g, '');
        if (!a && !b && !c) { hiddenEl.value = ''; return; }
        let combined = a; if (b) combined += '-' + b; if (c) combined += '-' + c; hiddenEl.value = combined;
      }

      document.addEventListener('DOMContentLoaded', function () {
        wireAddRemove('educationRows', 'addEducation', 'education-row', 'histories_education', 'education');
        wireAddRemove('workRows', 'addWork', 'work-row', 'histories_work', 'work');
        wireAddRemove('licenseRows', 'addLicense', 'license-row', 'licenses', null);

        const form = document.querySelector('form');
        form?.addEventListener('submit', function () {
          reindexRows(document.getElementById('educationRows'), 'histories_education', 'education');
          reindexRows(document.getElementById('workRows'), 'histories_work', 'work');
          reindexRows(document.getElementById('licenseRows'), 'licenses', null);
          // join postal/phone hidden fields if present
          const a1 = document.getElementsByName('address_postal_part1')[0];
          const a2 = document.getElementsByName('address_postal_part2')[0];
          const ah = document.getElementsByName('address_postal')[0];
          if (a1 && a2 && ah) joinPostal(a1, a2, ah);
          const c1 = document.getElementsByName('contact_postal_part1')[0];
          const c2 = document.getElementsByName('contact_postal_part2')[0];
          const ch = document.getElementsByName('contact_postal')[0];
          if (c1 && c2 && ch) joinPostal(c1, c2, ch);
          const p1 = document.getElementsByName('phone_part1')[0];
          const p2 = document.getElementsByName('phone_part2')[0];
          const p3 = document.getElementsByName('phone_part3')[0];
          const ph = document.getElementsByName('phone')[0];
          if (p1 && p2 && p3 && ph) joinPhone(p1, p2, p3, ph);
          const cp1 = document.getElementsByName('contact_phone_part1')[0];
          const cp2 = document.getElementsByName('contact_phone_part2')[0];
          const cp3 = document.getElementsByName('contact_phone_part3')[0];
          const cph = document.getElementsByName('contact_phone')[0];
          if (cp1 && cp2 && cp3 && cph) joinPhone(cp1, cp2, cp3, cph);
        });
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  </body>
</html>

<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>職務経歴書編集</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- form-specific CSS (overrides) -->
    <link rel="stylesheet" href="{{ asset('form.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
      @media (prefers-color-scheme: dark) {
        body { color: rgba(255, 255, 255, 0.9) !important; }
        .card-title, .form-label { color: rgba(255, 255, 255, 0.9) !important; }
        .text-muted, .form-text, small { color: rgba(255, 255, 255, 0.6) !important; }
        .form-control, .form-select, textarea {
          background-color: rgba(15, 23, 42, 0.6) !important;
          color: white !important;
          border-color: rgba(71, 85, 105, 0.5) !important;
        }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.4) !important; }
        .btn-outline-secondary { color: rgba(255, 255, 255, 0.8); border-color: rgba(255, 255, 255, 0.3); }
        .btn-outline-secondary:hover { background-color: rgba(255, 255, 255, 0.1); color: white; }
        hr { border-color: rgba(255, 255, 255, 0.2) !important; }
      }
    </style>
  </head>
  <body class="antialiased bg-slate-50 dark:bg-slate-900 text-gray-800 dark:text-gray-100 selection:bg-teal-500 selection:text-white relative overflow-x-hidden">
    <!-- Geometric Grid Background -->
    <div class="fixed inset-0 -z-20 h-full w-full bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_80%_80%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none"></div>

    <!-- Glassmorphism blobs -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-teal-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob dark:mix-blend-screen -z-10 pointer-events-none"></div>
    <div class="fixed top-0 right-1/4 w-96 h-96 bg-blue-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000 dark:mix-blend-screen -z-10 pointer-events-none"></div>

    <div class="container py-4 relative z-10">
      <div class="mb-3">
          <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">ダッシュボードに戻る</a>
      </div>
      <form method="post" class="resume-form" action="{{ route('cvs.update', $cv) }}">
        @csrf
        @method('PUT')
        <div class="card shadow-2xl border border-white/50 dark:border-slate-700/50 bg-white/40 dark:bg-slate-800/40 backdrop-blur-xl sm:rounded-2xl" style="background-color: transparent !important;">
          <div class="card-body p-4 sm:p-6">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h3 class="card-title mb-0">職務経歴書編集</h3>
              </div>
              <div class="text-end">
                <button type="submit" class="btn btn-primary" style="background-color: #20c997; border-color: #20c997;">更新</button>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <div class="form-section">
                  <label class="form-label required">氏名</label>
                  <input type="text" name="name" class="form-control" value="{{ old('name', $cv->name) }}" placeholder="例: 山田 太郎">
                </div>

                <div class="form-section">
                  <label class="form-label">希望職種 (任意)</label>
                  <input type="text" name="desired_position" class="form-control" value="{{ old('desired_position', $cv->desired_position) }}" placeholder="例: Webエンジニア">
                </div>

                <hr>

                <div class="form-section">
                  <label class="form-label">職務経歴</label>
                  @php
                    $oldHistories = old('histories', $cv->histories->toArray());
                    $histCount = max(1, count($oldHistories));
                  @endphp
                  <div id="historyRows">
                    @for ($i = 0; $i < $histCount; $i++)
                      @php $h = $oldHistories[$i] ?? ['id'=>'','start_year'=>'','start_month'=>'','end_year'=>'','end_month'=>'','company_name'=>'','job_description'=>'']; @endphp
                      <div class="card mb-3 history-row" style="background-color: rgba(255,255,255,0.3); border: 1px solid rgba(0,0,0,0.1);">
                        <div class="card-body p-3">
                          @if(!empty($h['id']))
                            <input type="hidden" name="histories[{{$i}}][id]" value="{{ $h['id'] }}">
                          @endif
                          <div class="row g-2 mb-2">
                            <div class="col-md-6 d-flex align-items-center gap-2">
                              <span>入社:</span>
                              <input type="number" class="form-control form-control-sm" name="histories[{{$i}}][start_year]" placeholder="年(西暦)" value="{{ $h['start_year'] ?? '' }}" style="width: 80px;">
                              <span>年</span>
                              <select class="form-select form-select-sm" name="histories[{{$i}}][start_month]" style="width: 70px;">
                                <option value="">月</option>
                                @for($m=1; $m<=12; $m++)
                                  <option value="{{$m}}" @if(($h['start_month'] ?? '') == $m) selected @endif>{{$m}}</option>
                                @endfor
                              </select>
                              <span>月</span>
                            </div>
                            <div class="col-md-6 d-flex align-items-center gap-2">
                              <span>退社:</span>
                              <input type="number" class="form-control form-control-sm" name="histories[{{$i}}][end_year]" placeholder="年(西暦)" value="{{ $h['end_year'] ?? '' }}" style="width: 80px;">
                              <span>年</span>
                              <select class="form-select form-select-sm" name="histories[{{$i}}][end_month]" style="width: 70px;">
                                <option value="">月</option>
                                @for($m=1; $m<=12; $m++)
                                  <option value="{{$m}}" @if(($h['end_month'] ?? '') == $m) selected @endif>{{$m}}</option>
                                @endfor
                              </select>
                              <span>月</span>
                            </div>
                          </div>
                          <div class="mb-2">
                            <input type="text" class="form-control" name="histories[{{$i}}][company_name]" placeholder="会社名" value="{{ $h['company_name'] ?? '' }}">
                          </div>
                          <div class="mb-2">
                            <textarea class="form-control" name="histories[{{$i}}][job_description]" rows="3" placeholder="職務内容・実績など">{{ $h['job_description'] ?? '' }}</textarea>
                          </div>
                          <div class="text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-history">削除</button>
                          </div>
                        </div>
                      </div>
                    @endfor
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" id="addHistory" class="btn btn-sm btn-outline-teal" style="color: #20c997; border-color: #20c997;">職務経歴を追加</button>
                  </div>
                </div>

                <hr>

                <div class="form-section">
                  <label class="form-label">免許・資格（取得年・名称）</label>
                  @php $oldLicenses = old('licenses', $cv->licenses->toArray()); $licCount = max(1, count($oldLicenses)); @endphp
                  <div id="licenseRows">
                    @for ($i = 0; $i < $licCount; $i++)
                      @php $l = $oldLicenses[$i] ?? ['id'=>'','year'=>'','month'=>'','name'=>'']; @endphp
                      <div class="input-group mb-2 license-row">
                        @if(!empty($l['id']))
                          <input type="hidden" name="licenses[{{$i}}][id]" value="{{ $l['id'] }}">
                        @endif
                        <input type="number" class="form-control" name="licenses[{{$i}}][year]" placeholder="年(西暦)" value="{{ $l['year'] ?? '' }}" style="max-width: 100px;">
                        <span class="input-group-text">年</span>
                        <select class="form-select" name="licenses[{{$i}}][month]" style="max-width: 80px;">
                          <option value="">月</option>
                          @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @if(($l['month'] ?? '') == $m) selected @endif>{{ $m }}</option>
                          @endfor
                        </select>
                        <span class="input-group-text">月</span>
                        <input type="text" class="form-control" name="licenses[{{$i}}][name]" placeholder="資格名" value="{{ $l['name'] ?? '' }}">
                        <button type="button" class="btn btn-outline-secondary btn-remove-license">−</button>
                      </div>
                    @endfor
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" id="addLicense" class="btn btn-sm btn-outline-teal" style="color: #20c997; border-color: #20c997;">免許・資格を追加</button>
                  </div>
                </div>

                <hr>

                <div class="form-section">
                  <label class="form-label">志望の動機・自己PRなど</label>
                  <textarea name="motivation" class="form-control" rows="5">{{ old('motivation', $cv->motivation) }}</textarea>
                </div>

              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <script>
      // History row cloning
      const historyContainer = document.getElementById('historyRows');
      const addHistoryBtn = document.getElementById('addHistory');

      addHistoryBtn.addEventListener('click', () => {
        const rows = historyContainer.querySelectorAll('.history-row');
        const idx = rows.length;
        const newRow = document.createElement('div');
        newRow.className = 'card mb-3 history-row';
        newRow.style.cssText = 'background-color: rgba(255,255,255,0.3); border: 1px solid rgba(0,0,0,0.1);';
        
        let monthOptions = '<option value="">月</option>';
        for(let i=1; i<=12; i++) monthOptions += `<option value="${i}">${i}</option>`;

        newRow.innerHTML = `
          <div class="card-body p-3">
            <div class="row g-2 mb-2">
              <div class="col-md-6 d-flex align-items-center gap-2">
                <span>入社:</span>
                <input type="number" class="form-control form-control-sm" name="histories[${idx}][start_year]" placeholder="年(西暦)" style="width: 80px;">
                <span>年</span>
                <select class="form-select form-select-sm" name="histories[${idx}][start_month]" style="width: 70px;">
                  ${monthOptions}
                </select>
                <span>月</span>
              </div>
              <div class="col-md-6 d-flex align-items-center gap-2">
                <span>退社:</span>
                <input type="number" class="form-control form-control-sm" name="histories[${idx}][end_year]" placeholder="年(西暦)" style="width: 80px;">
                <span>年</span>
                <select class="form-select form-select-sm" name="histories[${idx}][end_month]" style="width: 70px;">
                  ${monthOptions}
                </select>
                <span>月</span>
              </div>
            </div>
            <div class="mb-2">
              <input type="text" class="form-control" name="histories[${idx}][company_name]" placeholder="会社名">
            </div>
            <div class="mb-2">
              <textarea class="form-control" name="histories[${idx}][job_description]" rows="3" placeholder="職務内容・実績など"></textarea>
            </div>
            <div class="text-end">
              <button type="button" class="btn btn-outline-danger btn-sm btn-remove-history">削除</button>
            </div>
          </div>
        `;
        historyContainer.appendChild(newRow);
      });

      historyContainer.addEventListener('click', (e) => {
        if(e.target.classList.contains('btn-remove-history')) {
          e.target.closest('.history-row').remove();
          // Re-index
          historyContainer.querySelectorAll('.history-row').forEach((row, idx) => {
            const idInput = row.querySelector('input[type="hidden"]');
            if(idInput) idInput.name = `histories[${idx}][id]`;
            row.querySelector('[name*="[start_year]"]').name = `histories[${idx}][start_year]`;
            row.querySelector('[name*="[start_month]"]').name = `histories[${idx}][start_month]`;
            row.querySelector('[name*="[end_year]"]').name = `histories[${idx}][end_year]`;
            row.querySelector('[name*="[end_month]"]').name = `histories[${idx}][end_month]`;
            row.querySelector('[name*="[company_name]"]').name = `histories[${idx}][company_name]`;
            row.querySelector('[name*="[job_description]"]').name = `histories[${idx}][job_description]`;
          });
        }
      });

      // License row cloning
      const licenseContainer = document.getElementById('licenseRows');
      const addLicenseBtn = document.getElementById('addLicense');

      addLicenseBtn.addEventListener('click', () => {
        const rows = licenseContainer.querySelectorAll('.license-row');
        const idx = rows.length;
        const newRow = document.createElement('div');
        newRow.className = 'input-group mb-2 license-row';
        
        let monthOptions = '<option value="">月</option>';
        for(let i=1; i<=12; i++) monthOptions += `<option value="${i}">${i}</option>`;

        newRow.innerHTML = `
          <input type="number" class="form-control" name="licenses[${idx}][year]" placeholder="年(西暦)" style="max-width: 100px;">
          <span class="input-group-text">年</span>
          <select class="form-select" name="licenses[${idx}][month]" style="max-width: 80px;">
            ${monthOptions}
          </select>
          <span class="input-group-text">月</span>
          <input type="text" class="form-control" name="licenses[${idx}][name]" placeholder="資格名">
          <button type="button" class="btn btn-outline-secondary btn-remove-license">−</button>
        `;
        licenseContainer.appendChild(newRow);
      });

      licenseContainer.addEventListener('click', (e) => {
        if(e.target.classList.contains('btn-remove-license')) {
          e.target.closest('.license-row').remove();
          // Re-index
          licenseContainer.querySelectorAll('.license-row').forEach((row, idx) => {
            const idInput = row.querySelector('input[type="hidden"]');
            if(idInput) idInput.name = `licenses[${idx}][id]`;
            row.querySelector('[name*="[year]"]').name = `licenses[${idx}][year]`;
            row.querySelector('[name*="[month]"]').name = `licenses[${idx}][month]`;
            row.querySelector('[name*="[name]"]').name = `licenses[${idx}][name]`;
          });
        }
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  </body>
</html>

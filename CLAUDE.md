# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

A Japanese-language Laravel 12 web app for creating resumes (履歴書) and CVs/work-history documents (職務経歴書). Users register via Laravel Breeze, build a resume/CV through multi-section forms, share it via a token-based public URL (no login required), and export it as a PDF.

## Commands

```bash
# Install
composer install
npm ci
php artisan storage:link   # required for uploaded photos to be publicly served

# Run dev server (PHP server + queue listener + log tailer + Vite, concurrently)
composer dev

# Tests (PHPUnit) — clears config cache first, then runs php artisan test
composer test
php artisan test --filter=SomeTestName
php artisan test tests/Feature/ResumeFormTest.php

# Code style (Laravel Pint) — CI runs this with --test
./vendor/bin/pint
./vendor/bin/pint --test

# Frontend build
npm run dev     # Vite dev/watch
npm run build   # production build (required before deploy; Blade views read public/build/manifest.json)
```

Local dev DB defaults to SQLite; tests force `DB_CONNECTION=sqlite` with an in-memory database (see `phpunit.xml`). CI uses MySQL instead.

## Architecture

### Parallel Resume / CV domains

The app has two near-identical document types that do **not** share a base class: `Resume` and `Cv`. Each has its own Controller, Service, FormRequests, and child models, following the same pattern:

- `Resume{Controller,Service}` + `ResumeHistory`, `ResumeLicense`, `ResumeProfile` (education/work history, licenses, motivation/self-PR text)
- `Cv{Controller,Service}` + `CvHistory`, `CvLicense` (work history with join/leave dates, licenses, desired position)

When changing behavior in one (e.g. photo upload, public-token logic, PDF export), check whether the equivalent change is needed in the other — they were built as copies of each other rather than sharing code.

### Guest vs. authenticated ownership model

Both Resume and Cv rows can exist without a `user_id` (guest-created). On `store()`:
- If authenticated, the record gets `user_id`.
- If a guest, a random `public_token` (32-char hex) is generated and used for URLs like `resumes/{resume}?token=...`. Access control in `show`/`pdf` compares this token with `hash_equals()`.

`resumes:cleanup` (`app/Console/Commands/CleanupGuestResumes.php`) deletes guest resumes (and their files/relations) older than N days; `resumes:tokenize` backfills tokens for old anonymous rows. Authorization for authenticated access goes through `ResumePolicy` (owner or admin via `User::isAdmin()`, which checks Spatie roles first, then falls back to a legacy `role` column).

### Service layer: differential sync of child records

`ResumeService`/`CvService` handle create vs. update differently for child collections (histories, licenses):
- `createFromRequest` — blind inserts.
- `updateFromRequest` — diffs incoming rows (keyed by `id`) against existing DB rows: matched ids update, unmatched incoming rows insert, DB rows absent from the payload get deleted.

License input also supports a legacy free-text fallback (`licenses_text`) parsed line-by-line with a regex for `YYYY MM name` — only used when the structured `licenses[]` array isn't present.

### PDF generation has a 3-tier fallback chain

In `ResumeController::pdf()` / `CvController::pdf()`, generation is attempted in this order and falls through on failure:
1. **External API** (`ExternalPdfService`) — used when `services.pdf.enabled` (`PDF_SERVICE_ENABLED` env var) is true. Supports `html2pdf.app` or `pdfshift` providers (`PDF_SERVICE_PROVIDER`), configured in `config/services.php`. This is the required path on shared hosting (e.g. XSERVER, see `DEPLOY.md`) where `wkhtmltopdf` cannot be installed.
2. **Snappy** (`barryvdh/laravel-snappy`) — used if the `snappy.pdf` binding is available and `Knp\Snappy\Pdf` exists.
3. **`PdfGenerator`** (`app/Services/PdfGenerator.php`) — a direct `wkhtmltopdf` binary shell-out via Symfony Process, configured through `config/snappy.php`.

If all three fail, the endpoint falls back to returning raw HTML with an `X-PDF-Error` header rather than erroring out.

### Image handling

Uploaded photos are processed with `intervention/image` (Imagick if the extension is loaded, else GD), cropped/resized to 300x420 with `CoverModifier`, and saved under `storage/app/public/photos/` (served via the `storage:link` symlink).

### Admin area

Routes under `admin/*` are gated by `EnsureUserIsAdmin` middleware (registered as `admin`), which checks `User::isAdmin()`. Covers resume moderation (`Admin\ResumeModerationController` — approve/reject/mark-reviewed, CSV export) and user management (`Admin\UserController`, full resource route).

### Route ordering gotcha

Literal routes like `resumes/export` and `admin/resumes/export` are registered **before** the `{resume}` wildcard route in `routes/web.php` — otherwise Laravel's route model binding would try to resolve "export" as a resume ID. Keep this ordering if adding new literal sub-paths under `resumes/` or `cvs/`.

## CI/CD

- `.github/workflows/ci.yml`: on push/PR to main/master — installs deps, builds assets, runs `composer test` against MySQL, then `./vendor/bin/pint --test`. PHP is pinned via `shivammathur/setup-php@2.35.5`.
- `.github/workflows/deploy.yml`: deploys to XSERVER shared hosting via rsync + SSH on push to main/master/feat/ci-add-workflow. Post-deploy runs cache/route/view caching and `migrate --force` over SSH. See `DEPLOY.md` for the full manual deployment runbook, permission requirements, and troubleshooting (`Vite manifest not found`, 500 errors, storage symlink issues).

## Notes

- `wkhtmltopdf` is unavailable on the production host (XSERVER) — `PDF_SERVICE_ENABLED=true` must be set in that environment; local/dev can rely on Snappy/PdfGenerator instead.
- Japanese strings are used directly in controllers/views (flash messages, CSV headers, error text) rather than through `resources/lang`/`ja.json` — follow the existing convention when adding user-facing text in these areas.

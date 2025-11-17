# Rirekisho App（履歴書アプリ）

**概要**
このリポジトリは日本語対応の履歴書作成アプリです。ユーザー登録（Laravel Breeze）により個別の履歴書を作成・編集し、公開用トークンで第三者に共有したり、PDF（印刷向け）を生成できます。

**主な機能**
- `ユーザー認証`: Laravel Breeze を利用したログイン／登録機能
- `履歴書 CRUD`: 学歴・職歴を複数追加できるフォーム、写真アップロード
- `公開トークン`: トークン付き公開 URL により非ログインユーザーへ履歴書を表示
- `PDF エクスポート`: wkhtmltopdf（snappy）を利用したサーバーサイド PDF 生成
- `印刷レイアウト`: `public/css/resume-print.css` にて印刷・PDF 用スタイルを提供
- `日本語ローカライズ`: `resources/lang/ja.json` 等を含む

**使用している主なライブラリ / ツール**
- `laravel/framework` — フレームワーク本体
- `laravel/breeze` — 認証
- `barryvdh/laravel-snappy`（または独自 `PdfGenerator`）— wkhtmltopdf ラッパー
- `vite` / `npm` — フロントエンド資産のビルド
- `phpunit` — テスト
- `pint` — コード整形

**重要なファイル**
- アプリケーション本体: `app/`
- ビュー: `resources/views/`（`resume/` 以下に作成/編集/表示の Blade がある）
- 印刷スタイル: `public/css/resume-print.css`
- CI ワークフロー: `.github/workflows/ci.yml`
- PDF 設定: `config/snappy.php`

**開発 / ローカル実行手順（macOS / zsh の例）**

1. 前提ツールを用意
```bash
# Homebrew を使う例
brew install php@8.2 node wkhtmltopdf
```

2. リポジトリをクローンして依存をインストール
```bash
git clone git@github.com:nekonoko114/rirekisho-app.git
cd rirekisho-app
composer install
npm ci
```

3. 環境変数を準備
```bash
cp .env.example .env
# .env を編集して DB 等を設定（例: DB_CONNECTION, DB_HOST, MAIL_*）
php artisan key:generate
```

4. ストレージリンク（写真の公開表示用）
```bash
php artisan storage:link
```

5. DB マイグレーションとシード
```bash
php artisan migrate
php artisan db:seed   # 任意
```

6. アセットとサーバー起動
```bash
npm run dev    # 開発モード
php artisan serve
```

ブラウザで `http://127.0.0.1:8000` を開きます。

**テストとコード整形**
- テスト実行（PHPUnit）: `composer test` または `php artisan test`
- Pint（整形チェック）: `./vendor/bin/pint --test`

**CI（GitHub Actions）について**
- `.github/workflows/ci.yml` を追加しています。PR 作成時に自動でテスト・Pint・アセットビルドを実行します。
- CI は `shivammathur/setup-php@2.35.5` を利用して PHP をセットアップするようピン留めしています。

**PDF 出力について**
- サーバー側で `wkhtmltopdf` を呼び出して PDF を作成します。`config/snappy.php` を確認し、本番環境に適切なバイナリが設定されていることを確認してください。

**注意点 / 運用メモ**
- 写真・ファイルの保存先を S3 等に変更する場合は `config/filesystems.php` と保存ロジックを更新してください。
- wkhtmltopdf は環境差（フォントやバージョン）で出力が変わるため、本番環境と同等のバイナリでテストしてください。

**よくあるトラブルと対処**
- `Vite manifest not found` — `npm run build` を実行して `public/build/manifest.json` を生成してください。
- CI 上で DB パスが見つからない — CI はインメモリ SQLite を使うか MySQL サービスを構成してください（`.github/workflows/ci.yml` を参照）。

---

必要であれば、この README をさらに詳しく（環境変数一覧、デプロイ手順、スクリーンショット、API ドキュメント）に拡張します。ご希望の追加項目を教えてください。

# Rirekisho App (履歴書アプリ) - 技術仕様および構成

このドキュメントは、履歴書アプリプロジェクトの全体的な技術構成と仕様をまとめたものです。

## 1. 概要
ユーザー登録（Laravel Breeze）により個別の履歴書を作成・編集し、公開用トークンで第三者に共有したり、PDFとして出力・印刷したりすることができるWebアプリケーションです。

## 2. 技術スタック

### バックエンド
- **言語:** PHP (^8.2)
- **フレームワーク:** Laravel (^12.0)
- **認証:** Laravel Breeze (Breezejp)
- **権限管理:** Spatie Laravel Permission (`spatie/laravel-permission`)
- **ログ管理:** Spatie Laravel Activitylog (`spatie/laravel-activitylog`)
- **PDF生成:** Laravel Snappy (`barryvdh/laravel-snappy`) - `wkhtmltopdf` のラッパー
- **画像処理:** Intervention Image (`intervention/image`)

### フロントエンド
- **ビルドツール:** Vite
- **CSSフレームワーク:** Tailwind CSS (^3.1), フォームプラグイン (`@tailwindcss/forms`)
- **UIコンポーネントライブラリ:** Bootstrap (^5.3.8) も依存に含まれている
- **JavaScriptフレームワーク:** Alpine.js (^3.4)
- **グラフ/チャート:** Chart.js (^4.5.1)
- **クライアントサイドPDF/画像生成:** jsPDF, html2canvas (必要に応じてフロントエンド側での描画やPDFエクスポートに使用)

### 開発・運用環境
- **データベース:** SQLite (デフォルト設定・テスト用), 任意のRDBMS (本番環境)
- **インフラ/コンテナ:** Docker (`compose.yaml`, Laravel Sail 対応)
- **テスト:** PHPUnit
- **静的解析・コードフォーマット:** Laravel Pint
- **CI/CD:** GitHub Actions (`.github/workflows/ci.yml` による自動テスト・静的解析)

## 3. 主要な機能とアーキテクチャ

### 3.1. ユーザー認証と管理
- **機能:** ユーザー登録、ログイン、パスワードリセット等の基本機能。
- **実装:** Laravel Breezeを用いており、素早く標準的な認証フローが組み込まれています。
- **拡張性:** `spatie/laravel-permission` が導入されており、将来的に管理者権限や特定ユーザー向けの機能制限（ロール/パーミッションベースのアクセス制御）を柔軟に追加可能です。

### 3.2. 履歴書のCRUD機能
- **機能:** 学歴や職歴を複数追加できるフォーム、及び履歴書への顔写真アップロード機能。
- **実装:** `intervention/image` を使用してアップロードされた顔写真のリサイズ等の処理を行っていると推測されます。写真は `storage` ディレクトリに保存され、シンボリックリンクにより一般公開されます。

### 3.3. 外部共有機能
- **機能:** トークン付きの公開URLを発行し、アカウントを持たない非ログインユーザー（面接官や採用担当者など）に履歴書を安全に共有。
- **実装:** ランダムな文字列等の公開用トークンを履歴書データと紐付け、特定のルートでトークンを検証することで表示を制御します。

### 3.4. PDF出力と印刷対応
- **サーバーサイド出力:** `wkhtmltopdf` と `barryvdh/laravel-snappy` を利用して、HTMLビューをサーバー上でPDFファイルに変換し、ダウンロードさせます。
- **クライアント出力:** `package.json` に `jspdf` と `html2canvas` が含まれているため、JavaScriptを使ってブラウザ上で動的にPDFを生成するアプローチも併用または実験されている可能性があります。
- **印刷スタイル:** `public/css/resume-print.css` によって、ブラウザからの直接印刷やPDF変換時に適した専用のレイアウトが適用されます。

## 4. ディレクトリ構成
標準的なLaravelプロジェクトの構成に従っています。
- `app/` - アプリケーションのビジネスロジック (コントローラー、モデルなど)
- `config/` - 設定ファイル群 (`snappy.php` などの固有設定を含む)
- `database/` - マイグレーション、ファクトリー、シーダー
- `public/` - 公開ディレクトリ (`css/resume-print.css` などが配置)
- `resources/` - Bladeビュー、言語ファイル (`ja.json`)、未コンパイルのアセット
- `routes/` - ルーティング定義 (`web.php` 等)
- `tests/` - PHPUnitによるテストコード

## 5. デプロイとインフラの考慮事項
- `.env.production` の存在は、本番運用時の設定が別管理されていることを示しています。
- **wkhtmltopdf の依存:** 本番環境（LinuxサーバーやDockerコンテナ等）には `wkhtmltopdf` のバイナリがインストールされている必要があり、環境間でフォントのレンダリング差分などが出ないよう注意が必要です。
- **画像の公開:** S3などの外部ストレージを利用する場合は、`config/filesystems.php` の設定を調整する必要があります。

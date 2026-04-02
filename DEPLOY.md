# XSERVER デプロイ手順書

## 前提条件

-   XSERVER アカウントと SSH アクセス
-   MySQL データベース作成済み
-   PHP 8.1+ が利用可能（確認済み: 8.1.32）
-   ドメイン設定済み

## 📋 デプロイチェックリスト

### 1. ローカルでの準備

```bash
# 本番用依存をインストール（node_modules は除外）
composer install --no-dev --optimize-autoloader

# フロントエンドのビルド
npm install
npm run build

# 不要ファイルを除外してアーカイブ作成
tar --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='.git' \
    --exclude='.env' \
    -czf resume-app.tar.gz .
```

### 2. XSERVER へのアップロード

#### 2-1. FTP/SFTP でファイルをアップロード

```
/home/youruser/
├── resume-app/          # Laravel プロジェクト本体（ここにアップロード）
└── your-domain.com/
    └── public_html/     # 公開ディレクトリ
```

#### 2-2. SSH で接続して解凍

```bash
ssh youruser@yourserver.xserver.jp
cd ~/
tar -xzf resume-app.tar.gz -C resume-app
cd resume-app
```

### 3. 環境設定

#### 3-1. .env ファイルを作成

```bash
cp .env.production .env
nano .env  # または vim .env
```

**必須設定項目:**

```env
APP_KEY=（後で生成）
APP_URL=https://your-domain.com
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

PDF_SERVICE_ENABLED=true
PDF_SERVICE_PROVIDER=html2pdf

MAIL_MAILER=smtp
MAIL_HOST=your-domain.com
MAIL_PORT=587
MAIL_USERNAME=your-email@your-domain.com
MAIL_PASSWORD=your_password
```

#### 3-2. APP_KEY を生成

```bash
php artisan key:generate --force
```

### 4. 依存インストールと最適化

```bash
# Composer の依存を確認（必要なら再実行）
composer install --no-dev --optimize-autoloader

# キャッシュクリア
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. データベースセットアップ

```bash
# マイグレーション実行
php artisan migrate --force

# 初期データ投入（必要な場合のみ）
# php artisan db:seed --force
```

### 6. ストレージとパーミッション設定

```bash
# ストレージディレクトリ作成
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# パーミッション設定
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# ストレージリンク作成（方法1: artisan コマンド）
php artisan storage:link

# 方法2: 手動でシンボリックリンク作成（方法1が失敗した場合）
# cd ~/your-domain.com/public_html
# ln -s ~/resume-app/storage/app/public storage
```

**注意**: XSERVER で `storage:link` が失敗する場合は、手動でシンボリックリンクを作成するか、`public/storage` を実ディレクトリにして直接アップロード先に指定してください。

### 7. 公開ディレクトリの設定

#### 方法 A: シンボリックリンク（推奨）

```bash
# 既存の public_html をバックアップ
mv ~/your-domain.com/public_html ~/your-domain.com/public_html.backup

# Laravel の public を公開ディレクトリにリンク
ln -s ~/resume-app/public ~/your-domain.com/public_html
```

#### 方法 B: .htaccess でリダイレクト

`~/your-domain.com/public_html/.htaccess` に追加:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ /home/youruser/resume-app/public/$1 [L]
</IfModule>
```

#### 方法 C: public の内容を直接コピー（非推奨）

```bash
cp -r ~/resume-app/public/* ~/your-domain.com/public_html/
```

この場合、`public_html/index.php` の `__DIR__.'/../vendor/autoload.php'` のパスを調整してください。

### 8. Cron 設定（スケジューラー）

XSERVER の管理画面から Cron を設定:

```
# 毎分実行
* * * * * cd /home/youruser/resume-app && php artisan schedule:run >> /dev/null 2>&1
```

または SSH から crontab を編集:

```bash
crontab -e

# 以下を追加
* * * * * cd /home/youruser/resume-app && php artisan schedule:run >> /dev/null 2>&1
```

### 9. 動作確認

1. ブラウザで `https://your-domain.com` にアクセス
2. 新規登録・ログインをテスト
3. 履歴書作成と写真アップロードをテスト
4. PDF 生成をテスト（外部サービス経由）
5. ゲスト保存（public_token）をテスト

### 10. トラブルシューティング

#### ログの確認

```bash
tail -f storage/logs/laravel.log
```

#### よくあるエラーと対処

**500 Internal Server Error**

-   パーミッション確認: `chmod -R 775 storage bootstrap/cache`
-   `.env` が正しいか確認
-   `php artisan config:cache` を再実行

**画像が表示されない**

-   `storage:link` が成功したか確認
-   パーミッション確認: `chmod -R 775 storage/app/public`
-   `.env` で `APP_URL` が正しいか確認

**PDF 生成エラー**

-   `.env` で `PDF_SERVICE_ENABLED=true` を確認
-   ログで API エラーを確認
-   無料枠の制限（100 件/日）に達していないか確認

**DB 接続エラー**

-   XSERVER の MySQL 設定を確認（ホスト名、ポート）
-   DB ユーザーに適切な権限があるか確認

#### キャッシュクリア（問題発生時）

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

### 11. セキュリティチェック

-   [ ] `APP_DEBUG=false` になっているか
-   [ ] `APP_KEY` が設定されているか
-   [ ] `.env` ファイルが web からアクセスできないか（通常は自動的に保護される）
-   [ ] HTTPS が有効か（XSERVER の無料 SSL 利用）
-   [ ] 不要なファイル（`.git`, `tests`, `.env.example` 等）を削除したか
-   [ ] `storage` と `bootstrap/cache` のパーミッションが適切か
-   [ ] 定期バックアップを設定したか

### 12. 保守・更新

#### コード更新時

```bash
# 新しいコードをアップロード後
cd ~/resume-app
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### バックアップ

定期的に以下をバックアップ:

-   データベース: `mysqldump -u user -p database > backup.sql`
-   アップロードファイル: `tar -czf storage-backup.tar.gz storage/app/public`
-   `.env` ファイル

## 📌 重要な注意事項

1. **wkhtmltopdf は使えません** → 必ず `PDF_SERVICE_ENABLED=true` を設定
2. **Supervisor は使えません** → キューは database ドライバーを使用
3. **メモリ・実行時間制限** → 大量データ処理は注意（現在: 1G, 180 秒）
4. **共有 IP** → 大量のメール送信は SMTP 制限に注意

## 🆘 サポート

問題が発生した場合:

1. `storage/logs/laravel.log` を確認
2. XSERVER のサポートに PHP/MySQL の設定を確認
3. 外部 PDF サービスの API ドキュメントを確認

## 参考リンク

-   [XSERVER マニュアル](https://www.xserver.ne.jp/manual/)
-   [Laravel デプロイ公式ドキュメント](https://laravel.com/docs/deployment)
-   [html2pdf.app](https://html2pdf.app/)

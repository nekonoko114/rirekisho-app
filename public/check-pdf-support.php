<?php
/**
 * XSERVER PDF サポート確認スクリプト
 * このファイルを public/ にアップロードして https://your-domain.com/check-pdf-support.php でアクセス
 */
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>PDF サポート確認</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
        h2 { border-bottom: 2px solid #333; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>PDF 生成サポート確認</h1>

        <h2>1. wkhtmltopdf の確認</h2>
        <?php
        // Check if wkhtmltopdf exists
        $wkhtmltopdfPath = null;
$commonPaths = [
    '/usr/bin/wkhtmltopdf',
    '/usr/local/bin/wkhtmltopdf',
    '/opt/wkhtmltopdf/bin/wkhtmltopdf',
];

foreach ($commonPaths as $path) {
    if (file_exists($path) && is_executable($path)) {
        $wkhtmltopdfPath = $path;
        break;
    }
}

// Try to find via which command
if (! $wkhtmltopdfPath) {
    @exec('which wkhtmltopdf 2>/dev/null', $output, $returnCode);
    if ($returnCode === 0 && ! empty($output[0])) {
        $wkhtmltopdfPath = trim($output[0]);
    }
}

if ($wkhtmltopdfPath) {
    echo '<p class="success">✓ wkhtmltopdf が見つかりました</p>';
    echo '<p>パス: <code>'.htmlspecialchars($wkhtmltopdfPath).'</code></p>';

    // Check version
    @exec(escapeshellarg($wkhtmltopdfPath).' --version 2>&1', $versionOutput);
    if (! empty($versionOutput)) {
        echo '<p>バージョン情報:</p>';
        echo '<pre>'.htmlspecialchars(implode("\n", $versionOutput)).'</pre>';
    }

    // Test PDF generation
    $testHtml = '<html><body><h1>テスト PDF</h1><p>日本語表示テスト</p></body></html>';
    $tmpHtml = sys_get_temp_dir().'/test_'.uniqid().'.html';
    $tmpPdf = sys_get_temp_dir().'/test_'.uniqid().'.pdf';

    file_put_contents($tmpHtml, $testHtml);
    $cmd = escapeshellarg($wkhtmltopdfPath).' '.escapeshellarg($tmpHtml).' '.escapeshellarg($tmpPdf).' 2>&1';
    @exec($cmd, $execOutput, $execReturn);

    if ($execReturn === 0 && file_exists($tmpPdf) && filesize($tmpPdf) > 0) {
        echo '<p class="success">✓ PDF 生成テスト成功</p>';
        echo '<p>生成されたファイルサイズ: '.filesize($tmpPdf).' bytes</p>';
    } else {
        echo '<p class="error">✗ PDF 生成テスト失敗</p>';
        echo '<pre>'.htmlspecialchars(implode("\n", $execOutput)).'</pre>';
    }

    @unlink($tmpHtml);
    @unlink($tmpPdf);

} else {
    echo '<p class="error">✗ wkhtmltopdf が見つかりません</p>';
    echo '<p class="warning">→ 外部 PDF サービスの利用を推奨します</p>';
}
?>

        <h2>2. PHP 拡張の確認</h2>
        <?php
$requiredExtensions = ['gd', 'imagick', 'pdo_mysql', 'mbstring', 'fileinfo', 'openssl'];
echo '<ul>';
foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $class = $loaded ? 'success' : 'error';
    $mark = $loaded ? '✓' : '✗';
    echo '<li class="'.$class.'">'.$mark.' '.htmlspecialchars($ext).'</li>';
}
echo '</ul>';
?>

        <h2>3. PHP 情報</h2>
        <p>PHP バージョン: <strong><?php echo PHP_VERSION; ?></strong></p>
        <p>メモリ上限: <strong><?php echo ini_get('memory_limit'); ?></strong></p>
        <p>最大実行時間: <strong><?php echo ini_get('max_execution_time'); ?> 秒</strong></p>
        <p>最大アップロードサイズ: <strong><?php echo ini_get('upload_max_filesize'); ?></strong></p>
        <p>最大POST サイズ: <strong><?php echo ini_get('post_max_size'); ?></strong></p>

        <h2>4. ディレクトリ書き込み権限</h2>
        <?php
$testDir = sys_get_temp_dir();
$testFile = $testDir.'/test_write_'.uniqid().'.txt';
$writable = @file_put_contents($testFile, 'test');

if ($writable !== false) {
    echo '<p class="success">✓ 一時ディレクトリへの書き込み可能</p>';
    echo '<p>一時ディレクトリ: <code>'.htmlspecialchars($testDir).'</code></p>';
    @unlink($testFile);
} else {
    echo '<p class="error">✗ 一時ディレクトリへの書き込み不可</p>';
}
?>

        <h2>推奨事項</h2>
        <?php if ($wkhtmltopdfPath) { ?>
            <p class="success">✓ wkhtmltopdf が利用可能です。現在の実装をそのまま使用できます。</p>
            <p><code>config/snappy.php</code> で以下のパスを設定してください:</p>
            <pre>'binary' => '<?php echo $wkhtmltopdfPath; ?>',</pre>
        <?php } else { ?>
            <p class="error">wkhtmltopdf が利用できません。以下の代替案を検討してください:</p>
            <ul>
                <li><strong>外部 PDF サービス</strong>: html2pdf.app, PDFShift, CloudConvert など</li>
                <li><strong>ブラウザ印刷</strong>: 印刷用 CSS を調整してユーザーにブラウザ印刷を案内</li>
                <li><strong>VPS への移行</strong>: wkhtmltopdf をインストール可能なサーバーへ移行</li>
            </ul>
        <?php } ?>

        <hr style="margin: 30px 0;">
        <p style="color: #666; font-size: 14px;">
            確認後、このファイルは削除してください（セキュリティのため）<br>
            <code>rm public/check-pdf-support.php</code>
        </p>
    </div>
</body>
</html>

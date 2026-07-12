<?php

$vendor = __DIR__.'/../vendor/autoload.php';
if (! file_exists($vendor)) {
    echo "vendor autoload not found\n";
    exit(1);
}
require $vendor;
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Resume;

$r = Resume::orderBy('id', 'desc')->first();
if ($r) {
    $r->reviewed_at = null;
    $r->reviewed_by = null;
    $r->save();
    echo 'marked:'.$r->id.PHP_EOL;
} else {
    echo 'none'.PHP_EOL;
}

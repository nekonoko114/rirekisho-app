<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary' => env('WKHTML_PDF_BINARY', '/usr/bin/wkhtmltopdf'),
        'timeout' => 30,
        'options' => [
            // Default to A4 landscape for resume printing
            'orientation' => 'Landscape',
            'page-size' => 'A4',
            // Control margins via CSS @page; set wkhtmltopdf margins to 0 to avoid double-counting
            'margin-top' => '14mm',
            'margin-bottom' => '0mm',
            'margin-left' => '8mm',
            'margin-right' => '8mm',
            // Restrict local file access and JavaScript execution by default for security
            'enable-local-file-access' => env('WKHTML_ENABLE_LOCAL_FILE_ACCESS', false),
            'disable-javascript' => true,
            // adjust zoom slightly to help fit content; increase if text becomes too small
            'zoom' => 100,
        ],
        'env' => [],
    ],

    'image' => [
        'enabled' => true,
        'binary' => env('WKHTML_IMG_BINARY', '/usr/bin/wkhtmltoimage'),
        'timeout' => false,
        'options' => [],
        'env' => [],
    ],

];

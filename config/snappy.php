<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary'  => env('WKHTML_PDF_BINARY', '/usr/bin/wkhtmltopdf'),
        'timeout' => false,
        'options' => [
            // Default to A4 landscape for resume printing
            'orientation' => 'Landscape',
            'page-size' => 'A4',
            // Control margins via CSS @page; set wkhtmltopdf margins to 0 to avoid double-counting
            'margin-top' => '8mm',
            'margin-bottom' => '8mm',
            'margin-left' => '8mm',
            'margin-right' => '8mm',
            // allow local file access if needed for assets
            'enable-local-file-access' => true,
            // adjust zoom slightly to help fit content; increase if text becomes too small
            'zoom' => 0.80,
        ],
        'env'     => [],
    ],

    'image' => [
        'enabled' => true,
        'binary'  => env('WKHTML_IMG_BINARY', '/usr/bin/wkhtmltoimage'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],

];


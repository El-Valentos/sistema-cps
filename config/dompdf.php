<?php

return [
    'show_warnings' => false,
    'orientation' => 'portrait',
    'defines' => [
        'DOMPDF_FONT_DIR' => storage_path('fonts/'),
        'DOMPDF_FONT_CACHE' => storage_path('fonts/'),
        'DOMPDF_TEMP_DIR' => sys_get_temp_dir(),
        'DOMPDF_CHROOT' => realpath(base_path()),
        'DOMPDF_UNICODE_ENABLED' => true,
        'DOMPDF_ENABLE_HTML5PARSER' => true,
        'DOMPDF_ENABLE_CSS_FLOAT' => true,
        'DOMPDF_ENABLE_REMOTE' => true,
        'DOMPDF_ENABLE_PHP' => false,
        'DOMPDF_DEFAULT_MEDIA_TYPE' => 'screen',
        'DOMPDF_DEFAULT_PAPER_SIZE' => 'letter',
        'DOMPDF_DEFAULT_FONT' => 'sans-serif',
        'DOMPDF_DPI' => 96,
        'DOMPDF_ENABLE_FONT_SUBSETTING' => true,
        'DOMPDF_ADMIN_USERNAME' => env('DOMPDF_ADMIN_USERNAME', 'cps_admin'),
        'DOMPDF_ADMIN_PASSWORD' => env('DOMPDF_ADMIN_PASSWORD', 'CPS2026!'),
    ],
];
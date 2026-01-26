<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Python Path
    |--------------------------------------------------------------------------
    |
    | Path to the Python executable in the virtual environment.
    | For Docker: /var/www/html/venv/bin/python3
    | For local macOS/Linux: base_path('venv/bin/python3')
    |
    */
    'python_path' => env('OCR_PYTHON_PATH', base_path('venv/bin/python3')),

    /*
    |--------------------------------------------------------------------------
    | Script Path
    |--------------------------------------------------------------------------
    |
    | Path to the OCR Python script.
    |
    */
    'script_path' => base_path('scripts/ocr.py'),

    /*
    |--------------------------------------------------------------------------
    | Default DPI
    |--------------------------------------------------------------------------
    |
    | Default DPI for OCR processing. Higher values produce better results
    | but take longer to process.
    |
    */
    'default_dpi' => (int) env('OCR_DEFAULT_DPI', 1000),

    /*
    |--------------------------------------------------------------------------
    | Maximum DPI
    |--------------------------------------------------------------------------
    |
    | Maximum allowed DPI to prevent excessive processing times.
    |
    */
    'max_dpi' => (int) env('OCR_MAX_DPI', 2000),

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | Default language for OCR. Use Tesseract language codes.
    | Examples: 'spa', 'eng', 'spa+eng'
    |
    */
    'default_lang' => env('OCR_DEFAULT_LANG', 'spa'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for OCR processing to complete.
    |
    */
    'timeout' => (int) env('OCR_TIMEOUT', 120),

    /*
    |--------------------------------------------------------------------------
    | Supported Extensions
    |--------------------------------------------------------------------------
    |
    | List of supported file extensions for OCR processing.
    |
    */
    'supported_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif', 'pdf'],

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | List of supported Tesseract language codes.
    | Make sure corresponding language packs are installed on the system.
    |
    */
    'supported_languages' => ['spa', 'eng', 'spa+eng'],
];

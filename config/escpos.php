<?php

return [
    'connection' => env('ESC_POS_PRINTER_CONNECTION', 'windows'),
    'name' => env('ESC_POS_PRINTER_NAME', ''),
    'host' => env('ESC_POS_PRINTER_HOST', '127.0.0.1'),
    'port' => env('ESC_POS_PRINTER_PORT', 9100),
];

<?php

return [
    'auto_block_minutes' => (int) env('SECURITY_AUTO_BLOCK_MINUTES', 30),
    'business_hours' => [
        'start_hour' => (int) env('SECURITY_BUSINESS_START_HOUR', 8),
        'end_hour' => (int) env('SECURITY_BUSINESS_END_HOUR', 17),
    ],
    'reports' => [
        'repeated_access_threshold' => (int) env('SECURITY_REPORT_ACCESS_LIMIT', 10),
        'repeated_access_window_minutes' => (int) env('SECURITY_REPORT_ACCESS_WINDOW_MINUTES', 10),
        'large_export_days' => (int) env('SECURITY_REPORT_LARGE_EXPORT_DAYS', 31),
    ],
];

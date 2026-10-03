<?php

return [
    'max_accuracy_m' => (float) env('TRACKING_MAX_ACCURACY_M', 1000),
    'max_reported_speed_kmh' => (float) env('TRACKING_MAX_REPORTED_SPEED_KMH', 180),
    'max_implied_speed_kmh' => (float) env('TRACKING_MAX_IMPLIED_SPEED_KMH', 220),
    'fresh_seconds' => (int) env('TRACKING_FRESH_SECONDS', 300),
    'max_future_skew_seconds' => (int) env('TRACKING_MAX_FUTURE_SKEW_SECONDS', 120),
];

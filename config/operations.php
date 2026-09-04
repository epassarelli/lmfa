<?php

return [
    'health' => [
        'scheduler_max_age_seconds' => (int) env('OPERATIONS_SCHEDULER_MAX_AGE_SECONDS', 600),
    ],
];

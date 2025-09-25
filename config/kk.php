<?php

return [
    'features' => [
        // Master switch for DB-backed evaluation questions
        // Keep FALSE until you're ready to read from DB in the UI.
        'db_questions' => env('KK_FEATURES_DB_QUESTIONS', false),
    ],
];

<?php

return [
    // TTL for invite
    'ttl_days' => 7,

    // Base storage root (public disk)
    'storage_root' => 'teams',

    // Files live under: teams/{team_id}/dogs/{dog_id}/...
    'dog_dir' => 'dogs',

    // PII fields on Dog to scrub if include_adopter_pii = false
    'pii_columns' => [
        'adopter_name',
        'adopter_email',
        'adopter_phone',
        'adopter_address',
    ],

    // Child models to reparent by team (if your schema has them)
    // Each entry: [modelClass, 'team_key' => 'team_id', 'dog_key' => 'dog_id', 'extra_where' => closure|null]
    'reparentables' => [
        // [\App\Models\DogNote::class,      'team_key' => 'team_id', 'dog_key' => 'dog_id'],
        // [\App\Models\Evaluation::class,   'team_key' => 'team_id', 'dog_key' => 'dog_id'],
        // [\App\Models\VetFile::class,      'team_key' => 'team_id', 'dog_key' => 'dog_id'],
        // [\App\Models\DietEntry::class,    'team_key' => 'team_id', 'dog_key' => 'dog_id'],
    ],

    // For excluding private notes when include_private_notes = false
    'private_note_model' => \App\Models\DogNote::class ?? null,
    'private_note_flag'  => 'is_private', // boolean column on notes
];

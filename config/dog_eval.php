<?php

return [

    /* ======================= Confidence ======================= */
    'Confidence' => [

        'confidence_env' => [
            'text'    => 'How does the dog respond to new or unfamiliar environments?',
            'type'    => 'radio',
            'options' => [
                'shut_down'   => ['label' => '1. Shut down completely (hides, freezes, panics)', 'score' => 1],
                'overwhelmed' => ['label' => '2. Very overwhelmed, needs coaxing to move', 'score' => 2],
                'hesitant'    => ['label' => '3. Hesitant, warmed up slowly after a day or two', 'score' => 3],
                'cautious'    => ['label' => '4. Mild caution, adjusted quickly with support', 'score' => 4],
                'confident'   => ['label' => '5. Confident and curious immediately', 'score' => 5],
            ],
        ],

        'confidence_tight_spaces' => [
            'text'    => 'How does the dog react to being moved through tight spaces (doorways, hallways, kennels)?',
            'type'    => 'radio',
            'options' => [
                'refuses_panics'     => ['label' => '1. Refuses or panics when guided', 'score' => 1],
                'moves_hesitantly'   => ['label' => '2. Moves hesitantly, often freezes', 'score' => 2],
                'with_encouragement' => ['label' => '3. Moves forward with encouragement', 'score' => 3],
                'willing_min_hes'    => ['label' => '4. Moves willingly with minimal hesitation', 'score' => 4],
                'confident_move'     => ['label' => '5. Confidently moves through without issue', 'score' => 5],
            ],
        ],

        'confidence_settle' => [
            'text'    => 'How would you describe the dog’s ability to settle and rest over the course of a normal day?',
            'type'    => 'radio',
            'options' => [
                'shut_down'  => ['label' => '1. Shut down, unsettled, acts depressed or helpless', 'score' => 1],
                'anxious'    => ['label' => '2. Anxious, hyper-alert, or full of energy — doesn’t rest or rests briefly', 'score' => 2],
                'semi_restless' => ['label' => '3. Semi restless, periods of calm and unsettledness', 'score' => 3],
                'neutral'    => ['label' => '4. Neutral most of the time — settles independently when quiet', 'score' => 4],
                'relaxed'    => ['label' => '5. Relaxed, resilient, adaptable — regularly rests and naps', 'score' => 5],
            ],
        ],

        'confidence_calm_after_arousal' => [
            'text'    => 'How does your dog calm down after something exciting, frustrating, or overwhelming?',
            'type'    => 'radio',
            'options' => [
                'cannot_settle' => ['label' => '1. Struggles to settle (pacing, accidents) and can’t relax for 10+ minutes', 'score' => 1],
                'stays_hyper'   => ['label' => '2. Stays hyper or anxious < 10 minutes; may excited-pee or redirect', 'score' => 2],
                'settles_with_help' => ['label' => '3. Takes a few minutes but settles with help/redirection', 'score' => 3],
                'self_calms'    => ['label' => '4. Calms within a few minutes on their own', 'score' => 4],
                'handles_well'  => ['label' => '5. Handles big emotions well and returns to calm almost immediately', 'score' => 5],
            ],
        ],

        'confidence_noise' => [
            'text'    => 'How does the dog react to loud or unexpected noises (door, thunder, things dropped)?',
            'type'    => 'radio',
            'options' => [
                'extreme_panic' => ['label' => '1. Extreme panic, bolting, or hiding', 'score' => 1],
                'very_startled' => ['label' => '2. Very startled, may shut down or tremble', 'score' => 2],
                'noticeable'    => ['label' => '3. Noticeable startle, but recovers', 'score' => 3],
                'normal'        => ['label' => '4. Normal startle, then resumes activity', 'score' => 4],
                'barely_notices'=> ['label' => '5. Barely notices, or recovers instantly', 'score' => 5],
            ],
        ],

        'confidence_surfaces' => [
            'text'    => 'How willing is the dog to walk on new surfaces (gravel, grass, tile, wood deck, unstable footing)?',
            'type'    => 'radio',
            'options' => [
                'refuses'          => ['label' => '1. Refuses, freezes, or panics', 'score' => 1],
                'extremely_hesitant'=> ['label' => '2. Extremely hesitant, needs lots of coaxing', 'score' => 2],
                'hesitant_attempts'=> ['label' => '3. Hesitant but attempts with encouragement', 'score' => 3],
                'investigates'     => ['label' => '4. Investigates with mild caution; may retreat then try again', 'score' => 4],
                'bold'             => ['label' => '5. Bold and curious with new textures/surfaces', 'score' => 5],
            ],
        ],

        'confidence_separation' => [
            'text'    => 'How confident is the dog when separated from primary handler/friend dog?',
            'type'    => 'radio',
            'options' => [
                'panics'        => ['label' => '1. Panics immediately (screams, claws, frantic pacing)', 'score' => 1],
                'whines_excess' => ['label' => '2. Whines/barks excessively, does not settle', 'score' => 2],
                'brief_vocal'   => ['label' => '3. Paces or vocalizes briefly, but tolerates separation', 'score' => 3],
                'mild_discomfort'=> ['label' => '4. Mild discomfort but can redirect to enrichment/tasks', 'score' => 4],
                'settles'       => ['label' => '5. Settles independently, relaxed, resumes normal behavior', 'score' => 5],
            ],
        ],

        'confidence_fight' => [
            'text'    => 'When stressed, does the dog react with "fight"?',
            'type'    => 'radio',
            'options' => [
                'aggression_mild' => ['label' => '1. Goes into offensive/defensive aggression under mild pressure', 'score' => 1],
                'growl_snap'      => ['label' => '2. Low growl/snaps under moderate stress', 'score' => 2],
                'warnings'        => ['label' => '3. Shows warning signs but can be de-escalated', 'score' => 3],
                'avoidance_first' => ['label' => '4. Tries avoidance before escalating', 'score' => 4],
                'no_fight'        => ['label' => '5. Does not react with "fight" mode', 'score' => 5],
                'na'              => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'confidence_flight' => [
            'text'    => 'When stressed, does the dog react in "flight"?',
            'type'    => 'radio',
            'options' => [
                'immediate_bolt' => ['label' => '1. Immediate bolt/panic response with no regard for environment', 'score' => 1],
                'attempts_escape'=> ['label' => '2. Attempts to escape, paces/frantically searches exit', 'score' => 2],
                'pulls_or_freezes'=> ['label' => '3. Pulls away or freezes, mildly concerned', 'score' => 3],
                'hesitant_engaged'=> ['label' => '4. Hesitant or avoidant, but remains engaged', 'score' => 4],
                'no_flight'       => ['label' => '5. Does not react with "flight" mode', 'score' => 5],
                'na'              => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'confidence_ocd' => [
            'text'    => 'Does the dog display OCD-like behaviors (tail chasing, suckling, shadow/light chasing, repetitive circles)?',
            'type'    => 'radio',
            'options' => [
                'constant'  => ['label' => '1. Constant, repetitive behaviors that interfere with functioning', 'score' => 1],
                'frequent'  => ['label' => '2. Frequent behaviors even in low-stress settings', 'score' => 2],
                'triggered' => ['label' => '3. Behaviors occur under stress or specific triggers', 'score' => 3],
                'rare'      => ['label' => '4. Rare/infrequent; dog is easily redirected', 'score' => 4],
                'none'      => ['label' => '5. No signs of compulsive or repetitive behavior', 'score' => 5],
            ],
        ],

        'confidence_personality' => [
            'text'    => 'What one or two options describe the dog’s personality most of the time?',
            'type'    => 'radio',
            'options' => [
                'nervous'     => ['label' => '1. Nervous, anxious, apprehensive — struggles to feel safe or confident', 'score' => 1],
                'pushy'       => ['label' => '2. Pushy, intense, or overly energetic — often disregards boundaries or cues', 'score' => 2],
                'sensitive'   => ['label' => '3. Sensitive but curious — responsive but distrusts easily', 'score' => 3],
                'lazy'        => ['label' => '4. Lazy, couch-potato-ish — calm, easygoing, balanced', 'score' => 4],
                'confident'   => ['label' => '5. Confident, playful, friendly — enjoys engagement, adapts quickly', 'score' => 5],
            ],
        ],
    ],

    /* ======================= Sociability ======================= */
    'Sociability' => [

        'sociability_strangers_crate' => [
            'text'    => 'How does the dog react to strangers approaching run/crate?',
            'type'    => 'radio',
            'options' => [
                'reactive'  => ['label' => '1. Barks, lunges, or gets overly aroused; frustrated/overwhelmed/reactive', 'score' => 1],
                'avoidant'  => ['label' => '2. Avoids eye contact or turns away; nervous/unsure/uncomfortable', 'score' => 2],
                'selective' => ['label' => '3. Fine with some, reactive with others (selective)', 'score' => 3],
                'excitable' => ['label' => '4. Gets excited (tail wags, jumping, whining, happy barking)', 'score' => 4],
                'calm'      => ['label' => '5. Stays calm, curious, and happy; watches/sniffs/wags nicely', 'score' => 5],
                'na'        => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'sociability_strangers_leash' => [
            'text'    => 'When on the leash, how does the dog react to seeing strangers?',
            'type'    => 'radio',
            'options' => [
                'defensive' => ['label' => '1. Barks, growls, lunges, or stiffens; upset or overly defensive', 'score' => 1],
                'fearful'   => ['label' => '2. Avoids or freezes; fearful, nervous, or overwhelmed', 'score' => 2],
                'varies'    => ['label' => '3. Varies by human; friendly with some, reactive with others', 'score' => 3],
                'eager'     => ['label' => '4. Excited and wants to greet; pulls/whines/jumps, not upset', 'score' => 4],
                'calm'      => ['label' => '5. Calm or curious; remains focused and under control', 'score' => 5],
                'na'        => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'sociability_cannot_interact_humans' => [
            'text'    => 'When your dog sees humans but can’t interact, how do they respond?',
            'type'    => 'radio',
            'options' => [
                'red_zone' => ['label' => '1. Extremely worked up ("Red Zone") — barking/jumping/trying to escape', 'score' => 1],
                'fomo'     => ['label' => '2. FOMO/antsy; frustrated they can’t join in', 'score' => 2],
                'awkward'  => ['label' => '3. Looks and disengages awkwardly; unsure/apprehensive', 'score' => 3],
                'curious'  => ['label' => '4. Curious/slightly interested but fairly calm and quiet', 'score' => 4],
                'neutral'  => ['label' => '5. Neutral or calm tail wag; no tension near people', 'score' => 5],
                'na'       => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'sociability_time_to_trust' => [
            'text'    => 'How long until the dog views someone as not a stranger anymore?',
            'type'    => 'radio',
            'options' => [
                'weeks'       => ['label' => '1. Weeks of daily interaction with minimal progress', 'score' => 1],
                'many'        => ['label' => '2. Many exposures needed; remains skeptical', 'score' => 2],
                'multiple'    => ['label' => '3. Multiple exposures lead to trust and comfort', 'score' => 3],
                'one_two'     => ['label' => '4. Comfortable after 1–2 positive interactions', 'score' => 4],
                'everyone'    => ['label' => '5. Everyone is a friend, no one is a stranger', 'score' => 5],
            ],
        ],

        'sociability_guarding_humans' => [
            'text'    => 'How does the dog react when a person attempts to remove a toy, bone, or food item?',
            'type'    => 'radio',
            'options' => [
                'intense_guard' => ['label' => '1. Shows intense guarding, growling, snapping, or biting', 'score' => 1],
                'stiff_freeze'  => ['label' => '2. Stiffens, freezes, or shows visible discomfort', 'score' => 2],
                'reluctant'     => ['label' => '3. Reluctant but allows removal with management', 'score' => 3],
                'mild_protest'  => ['label' => '4. Mild protest (follows the item) but no escalation', 'score' => 4],
                'calm_allows'   => ['label' => '5. Calmly allows removal with no concern', 'score' => 5],
            ],
        ],

        'sociability_crate_dog_walkby' => [
            'text'    => 'How does your dog react when another dog walks past their crate/kennel/fenced area?',
            'type'    => 'radio',
            'options' => [
                'reactive'  => ['label' => '1. Barks, lunges, or gets overly aroused; reactive', 'score' => 1],
                'avoidant'  => ['label' => '2. Avoids/turns away; nervous or uncomfortable', 'score' => 2],
                'selective' => ['label' => '3. Fine with some, reactive with others (dog selective)', 'score' => 3],
                'excited'   => ['label' => '4. Gets excited but stays manageable (may pace/whine)', 'score' => 4],
                'calm'      => ['label' => '5. Calm/curious/neutral; may watch quietly, sniff, or ignore', 'score' => 5],
                'na'        => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'sociability_dogs_leash' => [
            'text'    => 'When your dog is on a leash and sees another dog nearby, how do they react?',
            'type'    => 'radio',
            'options' => [
                'defensive' => ['label' => '1. Barks, growls, lunges, or stiffens; upset or overly defensive', 'score' => 1],
                'fearful'   => ['label' => '2. Avoids or freezes; fearful/nervous/overwhelmed', 'score' => 2],
                'varies'    => ['label' => '3. Varies by dog; friendly with some, reactive with others', 'score' => 3],
                'eager'     => ['label' => '4. Excited and wants to greet; pulls/whines/jumps (not upset)', 'score' => 4],
                'calm'      => ['label' => '5. Calm or curious; focused and under control', 'score' => 5],
                'na'        => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'sociability_cannot_interact_dogs' => [
            'text'    => 'When your dog sees other dogs off-leash but can’t interact, how do they respond?',
            'type'    => 'radio',
            'options' => [
                'red_zone' => ['label' => '1. Extremely worked up ("Red Zone") — barking/jumping/trying to escape', 'score' => 1],
                'fomo'     => ['label' => '2. FOMO/antsy; frustrated they can’t join in', 'score' => 2],
                'awkward'  => ['label' => '3. Looks and disengages awkwardly; unsure/apprehensive', 'score' => 3],
                'curious'  => ['label' => '4. Curious/slightly interested but fairly calm and quiet', 'score' => 4],
                'neutral'  => ['label' => '5. Neutral or calm tail wag; no tension near dogs playing', 'score' => 5],
                'na'       => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'sociability_meeting_dogs' => [
            'text'    => 'How does the dog respond to meeting unfamiliar dogs?',
            'type'    => 'radio',
            'options' => [
                'dislikes_most' => ['label' => '1. Actively dislikes or reacts to most new dogs', 'score' => 1],
                'struggles_mismatch' => ['label' => '2. Struggles with same gender or different energy/play styles', 'score' => 2],
                'selective'     => ['label' => '3. Selective; gets along with some dogs but not others', 'score' => 3],
                'neutral_tolerant'=> ['label' => '4. Generally neutral or tolerant with slow introductions', 'score' => 4],
                'friendly'      => ['label' => '5. Friendly and appropriate with most new dogs', 'score' => 5],
                'na'            => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'sociability_resource_guard_dogs' => [
            'text'    => 'How does the dog react when a dog attempts to share a toy or affection from handler?',
            'type'    => 'radio',
            'options' => [
                'intense_guard' => ['label' => '1. Shows intense guarding, growling, snapping, or biting', 'score' => 1],
                'stiff_freeze'  => ['label' => '2. Stiffens, freezes, or shows visible discomfort', 'score' => 2],
                'reluctant'     => ['label' => '3. Reluctant but allows', 'score' => 3],
                'mild_protest'  => ['label' => '4. Mild protest but no escalation', 'score' => 4],
                'na'            => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'sociability_bite_person' => [
            'text'    => 'Has the dog ever bitten a person?',
            'type'    => 'radio',
            'options' => [
                'injury_unprovoked' => ['label' => '1. Yes, resulted in injury; unprovoked or intense', 'score' => 1],
                'pressure_no_blood' => ['label' => '2. Yes, bite with pressure, no blood; gave warning', 'score' => 2],
                'high_stress'       => ['label' => '3. Yes, in high-stress/fearful situation; pushed too far', 'score' => 3],
                'no_bite_warn'      => ['label' => '4. No bites, but has snapped or growled as a warning', 'score' => 4],
                'no_history'        => ['label' => '5. No history of biting, snapping, or aggressive contact', 'score' => 5],
            ],
        ],

        'sociability_bite_dog' => [
            'text'    => 'Has the dog ever bitten a dog?',
            'type'    => 'radio',
            'options' => [
                'injury_unprovoked' => ['label' => '1. Yes, resulted in injury; unprovoked or intense', 'score' => 1],
                'pressure_no_blood' => ['label' => '2. Yes, bite with pressure, no blood; gave warning', 'score' => 2],
                'high_stress'       => ['label' => '3. Yes, in high-stress/fearful situation; pushed too far', 'score' => 3],
                'no_bite_warn'      => ['label' => '4. No bites, but has snapped or growled as a warning', 'score' => 4],
                'no_history'        => ['label' => '5. No history of biting, snapping, or aggressive contact', 'score' => 5],
            ],
        ],
    ],

    /* ======================= Trainability ======================= */
    'Trainability' => [

        'trainability_motivation' => [
            'text'    => 'What types of things motivate the dog during training or engagement? (Dropdown)',
            'type'    => 'radio', // dropdown in UI, but radio for data model
            'options' => [
                'food'        => ['label' => '1. Food/treats', 'score' => 5],
                'toys'        => ['label' => '2. Toys (tug, fetch, chew with humans)', 'score' => 5],
                'interaction' => ['label' => '3. Playful human interaction', 'score' => 5],
                'affection'   => ['label' => '4. Physical affection (petting, praise, snuggling)', 'score' => 5],
                'unmotivated' => ['label' => '5. Dog appears unmotivated or shuts down when prompted', 'score' => 1],
            ],
        ],

        'trainability_impulse_control' => [
            'text'    => 'How well can your dog control themselves when excited or unsure?',
            'type'    => 'radio',
            'options' => [
                'no_control'   => ['label' => '1. No impulse control; charges ahead, jumps, grabs things', 'score' => 1],
                'struggles'    => ['label' => '2. Struggles a lot; occasionally pauses but usually impulsive', 'score' => 2],
                'some_control' => ['label' => '3. Some control in calm settings; struggles when excited', 'score' => 3],
                'good_control' => ['label' => '4. Pretty good control; listens and can wait with distractions', 'score' => 4],
                'excellent'    => ['label' => '5. Excellent control; calm, responsive, waits even when excited', 'score' => 5],
                'too_nervous'  => ['label' => '6. Too nervous to tell yet', 'score' => null],
            ],
        ],

        'trainability_focus' => [
            'text'    => 'How well can the dog focus around distractions?',
            'type'    => 'radio',
            'options' => [
                'none'      => ['label' => '1. No ability to focus; full shutdown or frantic behavior', 'score' => 1],
                'rare'      => ['label' => '2. Rarely focuses even with heavy management', 'score' => 2],
                'redirected'=> ['label' => '3. Focuses with frequent redirection and breaks', 'score' => 3],
                'maintains' => ['label' => '4. Maintains focus most of the time with reinforcement', 'score' => 4],
                'high'      => ['label' => '5. Highly focused and responsive with mild distractions', 'score' => 5],
            ],
        ],

        'trainability_barking' => [
            'text'    => 'Does the dog bark or whine persistently?',
            'type'    => 'radio',
            'options' => [
                'random_noises' => ['label' => '1. Yes at random noises or basically nothing', 'score' => 1],
                'demand'        => ['label' => '2. Yes at handler when demanding things', 'score' => 2],
                'walkbys'       => ['label' => '3. Yes at people/dogs walking by or joining other dogs barking', 'score' => 3],
                'startle_only'  => ['label' => '4. No, only when startled or excited', 'score' => 4],
                'good_baseline' => ['label' => '5. No, good baseline of not barking', 'score' => 5],
            ],
        ],

        'trainability_problem_solving' => [
            'text'    => 'When facing something unfamiliar (new game/obstacle/challenge), how do they react?',
            'type'    => 'radio',
            'options' => [
                'avoids'        => ['label' => '1. Avoids trying; afraid/overwhelmed/shuts down', 'score' => 1],
                'hesitant_try'  => ['label' => '2. Wants to try but hesitates; suspicious or gives up quickly', 'score' => 2],
                'tries_gives_up'=> ['label' => '3. Tries but gives up fast; frustrated or distracted', 'score' => 3],
                'tries_while'   => ['label' => '4. Tries for a while; works with encouragement', 'score' => 4],
                'persists'      => ['label' => '5. Keeps trying; persistence/curiosity/problem-solving', 'score' => 5],
            ],
        ],

        'trainability_leash_pressure' => [
            'text'    => 'How does the dog respond to leash pressure?',
            'type'    => 'radio',
            'options' => [
                'fights'      => ['label' => '1. Fights leash; flails or panics', 'score' => 1],
                'resists'     => ['label' => '2. Resists heavily; avoids direction; bites leash', 'score' => 2],
                'hesitant'    => ['label' => '3. Hesitant but can be guided', 'score' => 3],
                'follows'     => ['label' => '4. Follows direction with light encouragement', 'score' => 4],
                'responsive'  => ['label' => '5. Responsive and comfortable with leash handling', 'score' => 5],
            ],
        ],

        'trainability_verbal_guidance' => [
            'text'    => 'How does the dog react to verbal guidance or corrections (e.g., saying “no” when they jump up)?',
            'type'    => 'radio',
            'options' => [
                'agitated'   => ['label' => '1. Becomes agitated/defensive or shuts down completely', 'score' => 1],
                'confused'   => ['label' => '2. Appears confused/anxious; doesn’t change behavior', 'score' => 2],
                'learns_rep' => ['label' => '3. Initially unsure but responds with repetition', 'score' => 3],
                'listens'    => ['label' => '4. Listens and adjusts after one or two reminders', 'score' => 4],
                'immediate'  => ['label' => '5. Immediately responsive; changes behavior calmly', 'score' => 5],
            ],
        ],

        'trainability_care_reactivity' => [
            'text'    => 'Does the dog act poorly to the following types of care? (check all that apply)',
            'type'    => 'checkbox',
            'options' => [
                'nail_trimming'  => ['label' => 'Nail trimming'],
                'brushing'       => ['label' => 'Brushing/combing'],
                'oral_meds'      => ['label' => 'Giving medicine orally'],
                'ear_cleaning'   => ['label' => 'Ear cleaning/ear drops'],
                'bathing'        => ['label' => 'Bathing/blow drying'],
            ],
        ],

        'trainability_cats' => [
            'text'    => 'Has the dog been tested with cats? If so, how did they respond?',
            'type'    => 'radio',
            'options' => [
                'strong_prey'  => ['label' => '1. Strong prey drive; chases/fixates/attempts to harm', 'score' => 1],
                'high_interest'=> ['label' => '2. Highly interested; tries to chase; hard to redirect', 'score' => 2],
                'curious_manage'=> ['label' => '3. Curious but manageable; needs supervision', 'score' => 3],
                'respectful'   => ['label' => '4. Respectful or indifferent; redirects well', 'score' => 4],
                'cat_safe'     => ['label' => '5. Calm and cat-safe; coexists with no concern', 'score' => 5],
                'na'           => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],

        'trainability_prey_drive' => [
            'text'    => 'How strong is the dog’s prey drive (interest in chasing small animals/birds/movement)?',
            'type'    => 'radio',
            'options' => [
                'extremely_high' => ['label' => '1. Extremely high; lunges/fixates/attempts to chase instantly', 'score' => 1],
                'high'           => ['label' => '2. High; very interested; requires effort to redirect', 'score' => 2],
                'moderate'       => ['label' => '3. Moderate; notices and shows interest but can be redirected', 'score' => 3],
                'low'            => ['label' => '4. Low; brief curiosity; no strong urge to chase', 'score' => 4],
                'very_low'       => ['label' => '5. Very low; ignores squirrels/birds/fast movement', 'score' => 5],
                'na'             => ['label' => 'N/A (do not know)', 'score' => null],
            ],
        ],
    ],
];

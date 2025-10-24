<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\EvaluationForm;
use App\Models\EvaluationSection;
use App\Models\EvaluationFormQuestion;
use App\Models\Question;
use App\Models\AnswerOption;

class EvaluationFormV1Seeder extends Seeder
{
    public function run(): void
    {
        // ---- Target form (required) ----
        /** @var \App\Models\EvaluationForm $form */
        $form = EvaluationForm::findOrFail(1);

        // ---- Domain mapping (array key -> internal category + section title/slug) ----
        $domainMap = [
            'Confidence'   => ['cat' => 'comfort_confidence', 'title' => 'Comfort & Confidence', 'slug' => 'comfort-confidence', 'position' => 1],
            'Sociability'  => ['cat' => 'sociability',        'title' => 'Sociability',          'slug' => 'sociability',         'position' => 2],
            'Trainability' => ['cat' => 'trainability',       'title' => 'Trainability',         'slug' => 'trainability',        'position' => 3],
        ];

        // ---- Source data (scores are already 0–100) ----
        $data = [

            /* ======================= Confidence ======================= */
            'Confidence' => [

                'confidence_env' => [
                    'text'    => 'How does the dog respond to new or unfamiliar environments?',
                    'type'    => 'radio',
                    'options' => [
                        'shut_down'   => ['label' => '1. Shut down completely (hides, freezes, panics)', 'score' => 0],
                        'overwhelmed' => ['label' => '2. Very overwhelmed, needs coaxing to move', 'score' => 25],
                        'hesitant'    => ['label' => '3. Hesitant, warmed up slowly after a day or two', 'score' => 50],
                        'cautious'    => ['label' => '4. Mild caution, adjusted quickly with support', 'score' => 75],
                        'confident'   => ['label' => '5. Confident and curious immediately', 'score' => 100],
                    ],
                ],

                'confidence_tight_spaces' => [
                    'text'    => 'How does the dog react to being moved through tight spaces (doorways, hallways, kennels)?',
                    'type'    => 'radio',
                    'options' => [
                        'refuses_panics'     => ['label' => '1. Refuses or panics when guided', 'score' => 0],
                        'moves_hesitantly'   => ['label' => '2. Moves hesitantly, often freezes', 'score' => 25],
                        'with_encouragement' => ['label' => '3. Moves forward with encouragement', 'score' => 50],
                        'willing_min_hes'    => ['label' => '4. Moves willingly with minimal hesitation', 'score' => 75],
                        'confident_move'     => ['label' => '5. Confidently moves through without issue', 'score' => 100],
                    ],
                ],

                'confidence_settle' => [
                    'text'    => 'How would you describe the dog’s ability to settle and rest over the course of a normal day?',
                    'type'    => 'radio',
                    'options' => [
                        'shut_down'     => ['label' => '1. Shut down, unsettled, acts depressed or helpless', 'score' => 0],
                        'anxious'       => ['label' => '2. Anxious, hyper-alert, or full of energy — doesn’t rest or rests briefly', 'score' => 25],
                        'semi_restless' => ['label' => '3. Semi restless, periods of calm and unsettledness', 'score' => 50],
                        'neutral'       => ['label' => '4. Neutral most of the time — settles independently when quiet', 'score' => 75],
                        'relaxed'       => ['label' => '5. Relaxed, resilient, adaptable — regularly rests and naps', 'score' => 100],
                    ],
                ],

                'confidence_calm_after_arousal' => [
                    'text'    => 'How does your dog calm down after something exciting, frustrating, or overwhelming?',
                    'type'    => 'radio',
                    'options' => [
                        'cannot_settle'      => ['label' => '1. Struggles to settle (pacing, accidents) and can’t relax for 10+ minutes', 'score' => 0],
                        'stays_hyper'        => ['label' => '2. Stays hyper or anxious < 10 minutes; may excited-pee or redirect', 'score' => 25],
                        'settles_with_help'  => ['label' => '3. Takes a few minutes but settles with help/redirection', 'score' => 50],
                        'self_calms'         => ['label' => '4. Calms within a few minutes on their own', 'score' => 75],
                        'handles_well'       => ['label' => '5. Handles big emotions well and returns to calm almost immediately', 'score' => 100],
                    ],
                ],

                'confidence_noise' => [
                    'text'    => 'How does the dog react to loud or unexpected noises (door, thunder, things dropped)?',
                    'type'    => 'radio',
                    'options' => [
                        'extreme_panic'  => ['label' => '1. Extreme panic, bolting, or hiding', 'score' => 0],
                        'very_startled'  => ['label' => '2. Very startled, may shut down or tremble', 'score' => 25],
                        'noticeable'     => ['label' => '3. Noticeable startle, but recovers', 'score' => 50],
                        'normal'         => ['label' => '4. Normal startle, then resumes activity', 'score' => 75],
                        'barely_notices' => ['label' => '5. Barely notices, or recovers instantly', 'score' => 100],
                    ],
                ],

                'confidence_surfaces' => [
                    'text'    => 'How willing is the dog to walk on new surfaces (gravel, grass, tile, wood deck, unstable footing)?',
                    'type'    => 'radio',
                    'options' => [
                        'refuses'            => ['label' => '1. Refuses, freezes, or panics', 'score' => 0],
                        'extremely_hesitant' => ['label' => '2. Extremely hesitant, needs lots of coaxing', 'score' => 25],
                        'hesitant_attempts'  => ['label' => '3. Hesitant but attempts with encouragement', 'score' => 50],
                        'investigates'       => ['label' => '4. Investigates with mild caution; may retreat then try again', 'score' => 75],
                        'bold'               => ['label' => '5. Bold and curious with new textures/surfaces', 'score' => 100],
                    ],
                ],

                'confidence_separation' => [
                    'text'    => 'How confident is the dog when separated from primary handler/friend dog?',
                    'type'    => 'radio',
                    'options' => [
                        'panics'         => ['label' => '1. Panics immediately (screams, claws, frantic pacing)', 'score' => 0],
                        'whines_excess'  => ['label' => '2. Whines/barks excessively, does not settle', 'score' => 25],
                        'brief_vocal'    => ['label' => '3. Paces or vocalizes briefly, but tolerates separation', 'score' => 50],
                        'mild_discomfort'=> ['label' => '4. Mild discomfort but can redirect to enrichment/tasks', 'score' => 75],
                        'settles'        => ['label' => '5. Settles independently, relaxed, resumes normal behavior', 'score' => 100],
                    ],
                ],

                'confidence_fight' => [
                    'text'    => 'When stressed, does the dog react with "fight"?',
                    'type'    => 'radio',
                    'options' => [
                        'aggression_mild' => ['label' => '1. Goes into offensive/defensive aggression under mild pressure', 'score' => 0],
                        'growl_snap'      => ['label' => '2. Low growl/snaps under moderate stress', 'score' => 25],
                        'warnings'        => ['label' => '3. Shows warning signs but can be de-escalated', 'score' => 50],
                        'avoidance_first' => ['label' => '4. Tries avoidance before escalating', 'score' => 75],
                        'no_fight'        => ['label' => '5. Does not react with "fight" mode', 'score' => 100],
                        'na'              => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'confidence_flight' => [
                    'text'    => 'When stressed, does the dog react in "flight"?',
                    'type'    => 'radio',
                    'options' => [
                        'immediate_bolt'  => ['label' => '1. Immediate bolt/panic response with no regard for environment', 'score' => 0],
                        'attempts_escape' => ['label' => '2. Attempts to escape, paces/frantically searches exit', 'score' => 25],
                        'pulls_or_freezes'=> ['label' => '3. Pulls away or freezes, mildly concerned', 'score' => 50],
                        'hesitant_engaged'=> ['label' => '4. Hesitant or avoidant, but remains engaged', 'score' => 75],
                        'no_flight'       => ['label' => '5. Does not react with "flight" mode', 'score' => 100],
                        'na'              => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'confidence_ocd' => [
                    'text'    => 'Does the dog display OCD-like behaviors (tail chasing, suckling, shadow/light chasing, repetitive circles)?',
                    'type'    => 'radio',
                    'options' => [
                        'constant'  => ['label' => '1. Constant, repetitive behaviors that interfere with functioning', 'score' => 0],
                        'frequent'  => ['label' => '2. Frequent behaviors even in low-stress settings', 'score' => 25],
                        'triggered' => ['label' => '3. Behaviors occur under stress or specific triggers', 'score' => 50],
                        'rare'      => ['label' => '4. Rare/infrequent; dog is easily redirected', 'score' => 75],
                        'none'      => ['label' => '5. No signs of compulsive or repetitive behavior', 'score' => 100],
                    ],
                ],

                'confidence_personality' => [
                    'text'    => 'What one or two options describe the dog’s personality most of the time?',
                    'type'    => 'radio',
                    'options' => [
                        'nervous'   => ['label' => '1. Nervous, anxious, apprehensive — struggles to feel safe or confident', 'score' => 0],
                        'pushy'     => ['label' => '2. Pushy, intense, or overly energetic — often disregards boundaries or cues', 'score' => 25],
                        'sensitive' => ['label' => '3. Sensitive but curious — responsive but distrusts easily', 'score' => 50],
                        'lazy'      => ['label' => '4. Lazy, couch-potato-ish — calm, easygoing, balanced', 'score' => 75],
                        'confident' => ['label' => '5. Confident, playful, friendly — enjoys engagement, adapts quickly', 'score' => 100],
                    ],
                ],
            ],

            /* ======================= Sociability ======================= */
            'Sociability' => [

                'sociability_strangers_crate' => [
                    'text'    => 'How does the dog react to strangers approaching run/crate?',
                    'type'    => 'radio',
                    'options' => [
                        'reactive'  => ['label' => '1. Barks, lunges, or gets overly aroused; frustrated/overwhelmed/reactive', 'score' => 0],
                        'avoidant'  => ['label' => '2. Avoids eye contact or turns away; nervous/unsure/uncomfortable', 'score' => 25],
                        'selective' => ['label' => '3. Fine with some, reactive with others (selective)', 'score' => 50],
                        'excitable' => ['label' => '4. Gets excited (tail wags, jumping, whining, happy barking)', 'score' => 75],
                        'calm'      => ['label' => '5. Stays calm, curious, and happy; watches/sniffs/wags nicely', 'score' => 100],
                        'na'        => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'sociability_strangers_leash' => [
                    'text'    => 'When on the leash, how does the dog react to seeing strangers?',
                    'type'    => 'radio',
                    'options' => [
                        'defensive' => ['label' => '1. Barks, growls, lunges, or stiffens; upset or overly defensive', 'score' => 0],
                        'fearful'   => ['label' => '2. Avoids or freezes; fearful, nervous, or overwhelmed', 'score' => 25],
                        'varies'    => ['label' => '3. Varies by human; friendly with some, reactive with others', 'score' => 50],
                        'eager'     => ['label' => '4. Excited and wants to greet; pulls/whines/jumps, not upset', 'score' => 75],
                        'calm'      => ['label' => '5. Calm or curious; remains focused and under control', 'score' => 100],
                        'na'        => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'sociability_cannot_interact_humans' => [
                    'text'    => 'When your dog sees humans but can’t interact, how do they respond?',
                    'type'    => 'radio',
                    'options' => [
                        'red_zone' => ['label' => '1. Extremely worked up ("Red Zone") — barking/jumping/trying to escape', 'score' => 0],
                        'fomo'     => ['label' => '2. FOMO/antsy; frustrated they can’t join in', 'score' => 25],
                        'awkward'  => ['label' => '3. Looks and disengages awkwardly; unsure/apprehensive', 'score' => 50],
                        'curious'  => ['label' => '4. Curious/slightly interested but fairly calm and quiet', 'score' => 75],
                        'neutral'  => ['label' => '5. Neutral or calm tail wag; no tension near people', 'score' => 100],
                        'na'       => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'sociability_time_to_trust' => [
                    'text'    => 'How long until the dog views someone as not a stranger anymore?',
                    'type'    => 'radio',
                    'options' => [
                        'weeks'    => ['label' => '1. Weeks of daily interaction with minimal progress', 'score' => 0],
                        'many'     => ['label' => '2. Many exposures needed; remains skeptical', 'score' => 25],
                        'multiple' => ['label' => '3. Multiple exposures lead to trust and comfort', 'score' => 50],
                        'one_two'  => ['label' => '4. Comfortable after 1–2 positive interactions', 'score' => 75],
                        'everyone' => ['label' => '5. Everyone is a friend, no one is a stranger', 'score' => 100],
                    ],
                ],

                'sociability_guarding_humans' => [
                    'text'    => 'How does the dog react when a person attempts to remove a toy, bone, or food item?',
                    'type'    => 'radio',
                    'options' => [
                        'intense_guard' => ['label' => '1. Shows intense guarding, growling, snapping, or biting', 'score' => 0],
                        'stiff_freeze'  => ['label' => '2. Stiffens, freezes, or shows visible discomfort', 'score' => 25],
                        'reluctant'     => ['label' => '3. Reluctant but allows removal with management', 'score' => 50],
                        'mild_protest'  => ['label' => '4. Mild protest (follows the item) but no escalation', 'score' => 75],
                        'calm_allows'   => ['label' => '5. Calmly allows removal with no concern', 'score' => 100],
                    ],
                ],

                'sociability_crate_dog_walkby' => [
                    'text'    => 'How does your dog react when another dog walks past their crate/kennel/fenced area?',
                    'type'    => 'radio',
                    'options' => [
                        'reactive'  => ['label' => '1. Barks, lunges, or gets overly aroused; reactive', 'score' => 0],
                        'avoidant'  => ['label' => '2. Avoids/turns away; nervous or uncomfortable', 'score' => 25],
                        'selective' => ['label' => '3. Fine with some, reactive with others (dog selective)', 'score' => 50],
                        'excited'   => ['label' => '4. Gets excited but stays manageable (may pace/whine)', 'score' => 75],
                        'calm'      => ['label' => '5. Calm/curious/neutral; may watch quietly, sniff, or ignore', 'score' => 100],
                        'na'        => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'sociability_dogs_leash' => [
                    'text'    => 'When your dog is on a leash and sees another dog nearby, how do they react?',
                    'type'    => 'radio',
                    'options' => [
                        'defensive' => ['label' => '1. Barks, growls, lunges, or stiffens; upset or overly defensive', 'score' => 0],
                        'fearful'   => ['label' => '2. Avoids or freezes; fearful/nervous/overwhelmed', 'score' => 25],
                        'varies'    => ['label' => '3. Varies by dog; friendly with some, reactive with others', 'score' => 50],
                        'eager'     => ['label' => '4. Excited and wants to greet; pulls/whines/jumps (not upset)', 'score' => 75],
                        'calm'      => ['label' => '5. Calm or curious; focused and under control', 'score' => 100],
                        'na'        => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'sociability_cannot_interact_dogs' => [
                    'text'    => 'When your dog sees other dogs off-leash but can’t interact, how do they respond?',
                    'type'    => 'radio',
                    'options' => [
                        'red_zone' => ['label' => '1. Extremely worked up ("Red Zone") — barking/jumping/trying to escape', 'score' => 0],
                        'fomo'     => ['label' => '2. FOMO/antsy; frustrated they can’t join in', 'score' => 25],
                        'awkward'  => ['label' => '3. Looks and disengages awkwardly; unsure/apprehensive', 'score' => 50],
                        'curious'  => ['label' => '4. Curious/slightly interested but fairly calm and quiet', 'score' => 75],
                        'neutral'  => ['label' => '5. Neutral or calm tail wag; no tension near dogs playing', 'score' => 100],
                        'na'       => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'sociability_meeting_dogs' => [
                    'text'    => 'How does the dog respond to meeting unfamiliar dogs?',
                    'type'    => 'radio',
                    'options' => [
                        'dislikes_most'       => ['label' => '1. Actively dislikes or reacts to most new dogs', 'score' => 0],
                        'struggles_mismatch'  => ['label' => '2. Struggles with same gender or different energy/play styles', 'score' => 25],
                        'selective'           => ['label' => '3. Selective; gets along with some dogs but not others', 'score' => 50],
                        'neutral_tolerant'    => ['label' => '4. Generally neutral or tolerant with slow introductions', 'score' => 75],
                        'friendly'            => ['label' => '5. Friendly and appropriate with most new dogs', 'score' => 100],
                        'na'                  => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'sociability_resource_guard_dogs' => [
                    'text'    => 'How does the dog react when a dog attempts to share a toy or affection from handler?',
                    'type'    => 'radio',
                    'options' => [
                        'intense_guard' => ['label' => '1. Shows intense guarding, growling, snapping, or biting', 'score' => 0],
                        'stiff_freeze'  => ['label' => '2. Stiffens, freezes, or shows visible discomfort', 'score' => 25],
                        'reluctant'     => ['label' => '3. Reluctant but allows', 'score' => 50],
                        'mild_protest'  => ['label' => '4. Mild protest but no escalation', 'score' => 75],
                        'na'            => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'sociability_bite_person' => [
                    'text'    => 'Has the dog ever bitten a person?',
                    'type'    => 'radio',
                    'options' => [
                        'injury_unprovoked' => ['label' => '1. Yes, resulted in injury; unprovoked or intense', 'score' => 0],
                        'pressure_no_blood' => ['label' => '2. Yes, bite with pressure, no blood; gave warning', 'score' => 25],
                        'high_stress'       => ['label' => '3. Yes, in high-stress/fearful situation; pushed too far', 'score' => 50],
                        'no_bite_warn'      => ['label' => '4. No bites, but has snapped or growled as a warning', 'score' => 75],
                        'no_history'        => ['label' => '5. No history of biting, snapping, or aggressive contact', 'score' => 100],
                    ],
                ],

                'sociability_bite_dog' => [
                    'text'    => 'Has the dog ever bitten a dog?',
                    'type'    => 'radio',
                    'options' => [
                        'injury_unprovoked' => ['label' => '1. Yes, resulted in injury; unprovoked or intense', 'score' => 0],
                        'pressure_no_blood' => ['label' => '2. Yes, bite with pressure, no blood; gave warning', 'score' => 25],
                        'high_stress'       => ['label' => '3. Yes, in high-stress/fearful situation; pushed too far', 'score' => 50],
                        'no_bite_warn'      => ['label' => '4. No bites, but has snapped or growled as a warning', 'score' => 75],
                        'no_history'        => ['label' => '5. No history of biting, snapping, or aggressive contact', 'score' => 100],
                    ],
                ],
            ],

            /* ======================= Trainability ======================= */
            'Trainability' => [

                'trainability_motivation' => [
                    'text'    => 'What types of things motivate the dog during training or engagement? (Dropdown)',
                    'type'    => 'radio', // dropdown in UI, but radio for data model
                    'options' => [
                        'food'        => ['label' => '1. Food/treats', 'score' => 100],
                        'toys'        => ['label' => '2. Toys (tug, fetch, chew with humans)', 'score' => 100],
                        'interaction' => ['label' => '3. Playful human interaction', 'score' => 100],
                        'affection'   => ['label' => '4. Physical affection (petting, praise, snuggling)', 'score' => 100],
                        'unmotivated' => ['label' => '5. Dog appears unmotivated or shuts down when prompted', 'score' => 0],
                    ],
                ],

                'trainability_impulse_control' => [
                    'text'    => 'How well can your dog control themselves when excited or unsure?',
                    'type'    => 'radio',
                    'options' => [
                        'no_control'   => ['label' => '1. No impulse control; charges ahead, jumps, grabs things', 'score' => 0],
                        'struggles'    => ['label' => '2. Struggles a lot; occasionally pauses but usually impulsive', 'score' => 25],
                        'some_control' => ['label' => '3. Some control in calm settings; struggles when excited', 'score' => 50],
                        'good_control' => ['label' => '4. Pretty good control; listens and can wait with distractions', 'score' => 75],
                        'excellent'    => ['label' => '5. Excellent control; calm, responsive, waits even when excited', 'score' => 100],
                        'too_nervous'  => ['label' => '6. Too nervous to tell yet', 'score' => null],
                    ],
                ],

                'trainability_focus' => [
                    'text'    => 'How well can the dog focus around distractions?',
                    'type'    => 'radio',
                    'options' => [
                        'none'       => ['label' => '1. No ability to focus; full shutdown or frantic behavior', 'score' => 0],
                        'rare'       => ['label' => '2. Rarely focuses even with heavy management', 'score' => 25],
                        'redirected' => ['label' => '3. Focuses with frequent redirection and breaks', 'score' => 50],
                        'maintains'  => ['label' => '4. Maintains focus most of the time with reinforcement', 'score' => 75],
                        'high'       => ['label' => '5. Highly focused and responsive with mild distractions', 'score' => 100],
                    ],
                ],

                'trainability_barking' => [
                    'text'    => 'Does the dog bark or whine persistently?',
                    'type'    => 'radio',
                    'options' => [
                        'random_noises' => ['label' => '1. Yes at random noises or basically nothing', 'score' => 0],
                        'demand'        => ['label' => '2. Yes at handler when demanding things', 'score' => 25],
                        'walkbys'       => ['label' => '3. Yes at people/dogs walking by or joining other dogs barking', 'score' => 50],
                        'startle_only'  => ['label' => '4. No, only when startled or excited', 'score' => 75],
                        'good_baseline' => ['label' => '5. No, good baseline of not barking', 'score' => 100],
                    ],
                ],

                'trainability_problem_solving' => [
                    'text'    => 'When facing something unfamiliar (new game/obstacle/challenge), how do they react?',
                    'type'    => 'radio',
                    'options' => [
                        'avoids'         => ['label' => '1. Avoids trying; afraid/overwhelmed/shuts down', 'score' => 0],
                        'hesitant_try'   => ['label' => '2. Wants to try but hesitates; suspicious or gives up quickly', 'score' => 25],
                        'tries_gives_up' => ['label' => '3. Tries but gives up fast; frustrated or distracted', 'score' => 50],
                        'tries_while'    => ['label' => '4. Tries for a while; works with encouragement', 'score' => 75],
                        'persists'       => ['label' => '5. Keeps trying; persistence/curiosity/problem-solving', 'score' => 100],
                    ],
                ],

                'trainability_leash_pressure' => [
                    'text'    => 'How does the dog respond to leash pressure?',
                    'type'    => 'radio',
                    'options' => [
                        'fights'     => ['label' => '1. Fights leash; flails or panics', 'score' => 0],
                        'resists'    => ['label' => '2. Resists heavily; avoids direction; bites leash', 'score' => 25],
                        'hesitant'   => ['label' => '3. Hesitant but can be guided', 'score' => 50],
                        'follows'    => ['label' => '4. Follows direction with light encouragement', 'score' => 75],
                        'responsive' => ['label' => '5. Responsive and comfortable with leash handling', 'score' => 100],
                    ],
                ],

                'trainability_verbal_guidance' => [
                    'text'    => 'How does the dog react to verbal guidance or corrections (e.g., saying “no” when they jump up)?',
                    'type'    => 'radio',
                    'options' => [
                        'agitated'  => ['label' => '1. Becomes agitated/defensive or shuts down completely', 'score' => 0],
                        'confused'  => ['label' => '2. Appears confused/anxious; doesn’t change behavior', 'score' => 25],
                        'learns_rep'=> ['label' => '3. Initially unsure but responds with repetition', 'score' => 50],
                        'listens'   => ['label' => '4. Listens and adjusts after one or two reminders', 'score' => 75],
                        'immediate' => ['label' => '5. Immediately responsive; changes behavior calmly', 'score' => 100],
                    ],
                ],

                'trainability_care_reactivity' => [
                    'text'    => 'Does the dog act poorly to the following types of care? (check all that apply)',
                    'type'    => 'checkbox',
                    'options' => [
                        'nail_trimming' => ['label' => 'Nail trimming'],
                        'brushing'      => ['label' => 'Brushing/combing'],
                        'oral_meds'     => ['label' => 'Giving medicine orally'],
                        'ear_cleaning'  => ['label' => 'Ear cleaning/ear drops'],
                        'bathing'       => ['label' => 'Bathing/blow drying'],
                    ],
                ],

                'trainability_cats' => [
                    'text'    => 'Has the dog been tested with cats? If so, how did they respond?',
                    'type'    => 'radio',
                    'options' => [
                        'strong_prey'    => ['label' => '1. Strong prey drive; chases/fixates/attempts to harm', 'score' => 0],
                        'high_interest'  => ['label' => '2. Highly interested; tries to chase; hard to redirect', 'score' => 25],
                        'curious_manage' => ['label' => '3. Curious but manageable; needs supervision', 'score' => 50],
                        'respectful'     => ['label' => '4. Respectful or indifferent; redirects well', 'score' => 75],
                        'cat_safe'       => ['label' => '5. Calm and cat-safe; coexists with no concern', 'score' => 100],
                        'na'             => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],

                'trainability_prey_drive' => [
                    'text'    => 'How strong is the dog’s prey drive (interest in chasing small animals/birds/movement)?',
                    'type'    => 'radio',
                    'options' => [
                        'extremely_high' => ['label' => '1. Extremely high; lunges/fixates/attempts to chase instantly', 'score' => 0],
                        'high'           => ['label' => '2. High; very interested; requires effort to redirect', 'score' => 25],
                        'moderate'       => ['label' => '3. Moderate; notices and shows interest but can be redirected', 'score' => 50],
                        'low'            => ['label' => '4. Low; brief curiosity; no strong urge to chase', 'score' => 75],
                        'very_low'       => ['label' => '5. Very low; ignores squirrels/birds/fast movement', 'score' => 100],
                        'na'             => ['label' => 'N/A (do not know)', 'score' => null],
                    ],
                ],
            ],
        ];

        DB::transaction(function () use ($form, $domainMap, $data) {
            // ---- Ensure sections exist (fixed positions) ----
            $sections = [];
            foreach ($domainMap as $domain => $meta) {
                /** @var \App\Models\EvaluationSection $sec */
                $sec = EvaluationSection::firstOrCreate(
                    [
                        'form_id' => $form->id,
                        'slug'    => $meta['slug'],
                    ],
                    [
                        'title'    => $meta['title'],
                        'position' => $meta['position'],
                    ]
                );

                // keep title/position synchronized if changed
                $changed = false;
                if ($sec->title !== $meta['title']) { $sec->title = $meta['title']; $changed = true; }
                if ((int)$sec->position !== (int)$meta['position']) { $sec->position = (int)$meta['position']; $changed = true; }
                if ($changed) $sec->save();

                $sections[$domain] = $sec;
            }

            // ---- Seed questions/options per domain ----
            foreach ($data as $domain => $qs) {
                $catKey = $domainMap[$domain]['cat'];
                $sec    = $sections[$domain];

                // next position inside this section
                $nextPos = (int) EvaluationFormQuestion::where('form_id', $form->id)
                    ->where('section_id', $sec->id)
                    ->max('position');
                $nextPos = $nextPos > 0 ? $nextPos + 1 : 1;

                foreach ($qs as $qKey => $row) {
                    $prompt = trim($row['text'] ?? '');
                    if ($prompt === '') { continue; }

                    $typeMap = [
                        'radio'    => 'single_choice',
                        'checkbox' => 'multi_choice',
                        'text'     => 'text',
                        'scale'    => 'scale',
                        'boolean'  => 'boolean',
                    ];
                    $srcType = $row['type'] ?? 'radio';
                    $qType   = $typeMap[$srcType] ?? 'single_choice';

                    // deterministic slug: "<domain>.<key>"
                    $slugBase = Str::slug($domain.' '.$qKey);

                    // Upsert (by slug)
                    /** @var \App\Models\Question $question */
                    $question = Question::firstOrCreate(
                        ['slug' => $slugBase],
                        [
                            'slug'      => $slugBase,
                            'prompt'    => $prompt,
                            'help_text' => null,
                            'type'      => $qType,
                            'category'  => $catKey,
                            'meta'      => null,
                        ]
                    );

                    // Ensure correct domain/type/prompt
                    if ($question->category !== $catKey || $question->type !== $qType || $question->prompt !== $prompt) {
                        $question->category = $catKey;
                        $question->type     = $qType;
                        $question->prompt   = $prompt;
                        $question->save();
                    }

                    // Options for discrete types
                    if (in_array($qType, ['single_choice', 'multi_choice', 'boolean'], true)) {
                        $opts = Arr::get($row, 'options', []);
                        $oPos = 0;

                        foreach ($opts as $optKey => $optRow) {
                            $label = trim(Arr::get($optRow, 'label', ''));
                            if ($label === '') { continue; }
                            $oPos++;

                            // Use the pre-scaled 0–100 score directly (null allowed)
                            $scoreVal = Arr::has($optRow, 'score') ? Arr::get($optRow, 'score') : null;

                            // Score map only for the derived category; others zero
                            $scoreMap = [
                                'comfort_confidence' => $catKey === 'comfort_confidence' ? $scoreVal : 0,
                                'sociability'        => $catKey === 'sociability'        ? $scoreVal : 0,
                                'trainability'       => $catKey === 'trainability'       ? $scoreVal : 0,
                            ];

                            // Try to find existing option by (question_id + value or label)
                            $existing = AnswerOption::where('question_id', $question->id)
                                ->where(function ($q) use ($optKey, $label) {
                                    $q->where('value', $optKey)->orWhere('label', $label);
                                })->first();

                            if (!$existing) {
                                AnswerOption::create([
                                    'question_id' => $question->id,
                                    'label'       => $label,
                                    'value'       => $optKey, // keep stable internal value from provided key
                                    'position'    => $oPos,
                                    'score_map'   => $scoreMap,
                                    'flags'       => [],
                                ]);
                            } else {
                                // keep label/score_map/position synced
                                $changed = false;
                                if ($existing->label !== $label) { $existing->label = $label; $changed = true; }
                                if ($existing->position !== $oPos) { $existing->position = $oPos; $changed = true; }
                                $existing->score_map = $scoreMap;
                                $changed = true;
                                if ($changed) $existing->save();
                            }
                        }
                    }

                    // Attach to form/section if not already attached
                    $attached = EvaluationFormQuestion::where('form_id', $form->id)
                        ->where('section_id', $sec->id)
                        ->where('question_id', $question->id)
                        ->first();

                    if (!$attached) {
                        EvaluationFormQuestion::create([
                            'form_id'     => $form->id,
                            'section_id'  => $sec->id,
                            'question_id' => $question->id,
                            'position'    => $nextPos++,
                            'required'    => true,
                            'visibility'  => 'always',
                            'meta'        => null,
                        ]);
                    }
                }
            }
        });

        $this->command?->info('Evaluation Form (id=1) seeded with sections, questions, and options (scores stored directly as 0–100).');
    }

    private function uniqueSlug(string $table, string $column, string $base): string
    {
        $slug = $base;
        $i = 2;
        while (DB::table($table)->where($column, $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}

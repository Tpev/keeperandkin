<?php

namespace Database\Seeders;

use App\Models\AnswerOption;
use App\Models\EvaluationForm;
use App\Models\EvaluationFormQuestion;
use App\Models\EvaluationSection;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class EvaluationDefaultFormSeeder extends Seeder
{
    /**
     * Global default Keeper & Kin form (v1).
     * Categories:
     *  - comfort_confidence  => "Comfort & Confidence"
     *  - sociability         => "Sociability"
     *  - trainability        => "Trainability"
     *
     * Notes
     * -----
     * - Idempotent: uses slugs + upsert-ish logic so re-running is safe.
     * - Scoring is 0..100. Adjust mappings to mirror your current legacy logic.
     * - Add/replace questions below to match your existing in-code list.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1) Ensure a single GLOBAL active default form (team_id = null)
            $form = EvaluationForm::query()
                ->whereNull('team_id')
                ->where('slug', 'keeper-kin-default')
                ->where('version', 1)
                ->first();

            if (!$form) {
                // Deactivate any other global active forms to avoid ambiguity
                EvaluationForm::query()
                    ->whereNull('team_id')
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                $form = EvaluationForm::create([
                    'team_id'    => null,
                    'name'       => 'Keeper & Kin Default',
                    'slug'       => 'keeper-kin-default',
                    'version'    => 1,
                    'is_active'  => true,
                    'created_by' => null,
                    'updated_by' => null,
                ]);
            }

            // 2) Create sections (or fetch if exist)
            $sections = $this->upsertSections($form->id, [
                ['title' => 'Comfort & Confidence', 'slug' => 'comfort-confidence', 'position' => 1],
                ['title' => 'Sociability',          'slug' => 'sociability',         'position' => 2],
                ['title' => 'Trainability',         'slug' => 'trainability',        'position' => 3],
            ]);

            // 3) Define questions (EDIT HERE to mirror your legacy set)
            //    Types supported: single_choice, multi_choice, scale, boolean, text
            //    category: comfort_confidence | sociability | trainability | general
            //    meta can include scale config: {"min":0,"max":5,"invert":false}
            $qDefs = [

                // ===== Comfort & Confidence =====
                [
                    'section'  => 'comfort-confidence',
                    'question' => [
                        'slug'      => 'env-novelty-response',
                        'prompt'    => 'How does the dog respond to new environments (surfaces, noises, objects)?',
                        'help_text' => 'Observe first 2–3 minutes in a novel space.',
                        'type'      => 'single_choice',
                        'category'  => 'comfort_confidence',
                        'meta'      => null,
                    ],
                    'options'  => [
                        // label, value, score_map, flags[]
                        ['Very stressed (shuts down, trembles)', 'very_stressed', ['comfort_confidence' => 10], ['Shuts Down Under Pressure Flag']],
                        ['Cautious but recovers with support',    'cautious',      ['comfort_confidence' => 50], []],
                        ['Curious and explores confidently',      'confident',     ['comfort_confidence' => 90], []],
                    ],
                ],
                [
                    'section'  => 'comfort-confidence',
                    'question' => [
                        'slug'      => 'startle-recovery-time',
                        'prompt'    => 'Startle recovery time when surprised by a sudden noise.',
                        'help_text' => 'Time to return to baseline.',
                        'type'      => 'scale',
                        'category'  => 'comfort_confidence',
                        'meta'      => ['min' => 0, 'max' => 10, 'invert' => true], // 0s -> 100, 10s -> 0
                    ],
                    'options'  => [], // scale uses meta, no options
                ],

                // ===== Sociability =====
                [
                    'section'  => 'sociability',
                    'question' => [
                        'slug'      => 'human-greeting',
                        'prompt'    => 'Greeting with unfamiliar humans.',
                        'help_text' => 'Initial approach, body language, consent signals.',
                        'type'      => 'single_choice',
                        'category'  => 'sociability',
                        'meta'      => null,
                    ],
                    'options'  => [
                        ['Avoids/backs away or freezes', 'avoidant',  ['sociability' => 20], ['No Strangers']],
                        ['Neutral/accepting with guidance', 'neutral', ['sociability' => 60], []],
                        ['Friendly & seeks contact politely', 'friendly', ['sociability' => 90], []],
                    ],
                ],
                [
                    'section'  => 'sociability',
                    'question' => [
                        'slug'      => 'dog-dog-intro',
                        'prompt'    => 'Dog–dog introduction style with resident/neutral dog.',
                        'help_text' => 'Evaluate arousal and communication.',
                        'type'      => 'single_choice',
                        'category'  => 'sociability',
                        'meta'      => null,
                    ],
                    'options'  => [
                        ['Reactive/escalates', 'reactive', ['sociability' => 10], ['Dog Reactive Flag']],
                        ['Slow intros required', 'slow',   ['sociability' => 50], ['Slow Intros Dogs']],
                        ['Neutrality / respectful', 'neutrality', ['sociability' => 85], ['Neutrality Dogs']],
                    ],
                ],

                // ===== Trainability =====
                [
                    'section'  => 'trainability',
                    'question' => [
                        'slug'      => 'handler-focus',
                        'prompt'    => 'Maintains focus on handler in low-distraction setting.',
                        'help_text' => 'Eye contact for 5 seconds after cue.',
                        'type'      => 'single_choice',
                        'category'  => 'trainability',
                        'meta'      => null,
                    ],
                    'options'  => [
                        ['Struggles to focus', 'low',    ['trainability' => 25], []],
                        ['Intermittent focus', 'mid',    ['trainability' => 60], []],
                        ['Strong, sustained focus', 'high', ['trainability' => 90], []],
                    ],
                ],
                [
                    'section'  => 'trainability',
                    'question' => [
                        'slug'      => 'impulse-control',
                        'prompt'    => 'Impulse control on "Leave it".',
                        'help_text' => 'Food on open palm; release after eye contact.',
                        'type'      => 'single_choice',
                        'category'  => 'trainability',
                        'meta'      => null,
                    ],
                    'options'  => [
                        ['Fails without management', 'fails', ['trainability' => 20], ['Needs Impulse Control']],
                        ['Succeeds with prompts',    'cued',  ['trainability' => 55], []],
                        ['Succeeds reliably',        'good',  ['trainability' => 85], []],
                    ],
                ],
            ];

            // 4) Upsert questions and attach to form with options
            $posCounters = [
                'comfort-confidence' => 0,
                'sociability'        => 0,
                'trainability'       => 0,
            ];

            foreach ($qDefs as $def) {
                $sectionSlug = $def['section'];
                $section     = $sections[$sectionSlug] ?? null;
                if (!$section) {
                    // Skip if section missing (shouldn't happen)
                    continue;
                }

                $qData = $def['question'];
                $q     = Question::query()->where('slug', $qData['slug'])->first();

                if (!$q) {
                    $q = Question::create([
                        'slug'      => $qData['slug'],
                        'prompt'    => $qData['prompt'],
                        'help_text' => $qData['help_text'] ?? null,
                        'type'      => $qData['type'],
                        'category'  => $qData['category'],
                        'meta'      => $qData['meta'] ?? null,
                    ]);
                } else {
                    // Keep prompt/type/category in sync but avoid breaking changes if already used in prod
                    $q->update([
                        'prompt'    => $qData['prompt'],
                        'help_text' => $qData['help_text'] ?? null,
                        'type'      => $qData['type'],
                        'category'  => $qData['category'],
                        'meta'      => $qData['meta'] ?? null,
                    ]);
                }

                // Attach into the form (if not attached)
                $posCounters[$sectionSlug]++;
                $fq = EvaluationFormQuestion::query()
                    ->where('form_id', $form->id)
                    ->where('question_id', $q->id)
                    ->first();

                if (!$fq) {
                    $fq = EvaluationFormQuestion::create([
                        'form_id'    => $form->id,
                        'section_id' => $section->id,
                        'question_id'=> $q->id,
                        'position'   => $posCounters[$sectionSlug],
                        'required'   => true,
                        'visibility' => 'always',
                        'meta'       => null,
                    ]);
                } else {
                    // Ensure right section + position (don’t reorder if already set)
                    $fq->update([
                        'section_id' => $section->id,
                        'position'   => $fq->position ?: $posCounters[$sectionSlug],
                    ]);
                }

                // Options
                $this->syncOptions($q, $def['options'] ?? []);
            }
        });
    }

    /**
     * Create or update sections for a form.
     *
     * @return array<string, EvaluationSection> keyed by slug
     */
    protected function upsertSections(int $formId, array $defs): array
    {
        $out = [];
        foreach ($defs as $d) {
            $sec = EvaluationSection::query()
                ->where('form_id', $formId)
                ->where('slug', $d['slug'])
                ->first();

            if (!$sec) {
                $sec = EvaluationSection::create([
                    'form_id'  => $formId,
                    'title'    => $d['title'],
                    'slug'     => $d['slug'],
                    'position' => $d['position'] ?? 1,
                ]);
            } else {
                $sec->update([
                    'title'    => $d['title'],
                    'position' => $d['position'] ?? $sec->position,
                ]);
            }
            $out[$d['slug']] = $sec;
        }
        return $out;
    }

    /**
     * Sync answer options for a question (idempotent by label+value).
     * Removes nothing (safe in prod); only creates/updates listed ones.
     */
    protected function syncOptions(Question $q, array $defs): void
    {
        $position = 0;
        foreach ($defs as $o) {
            $position++;
            [$label, $value, $scoreMap, $flags] = [
                $o[0] ?? 'Option',
                $o[1] ?? null,
                $o[2] ?? [],
                $o[3] ?? [],
            ];

            $existing = AnswerOption::query()
                ->where('question_id', $q->id)
                ->where('label', $label)
                ->when($value !== null, fn($qb) => $qb->where('value', $value))
                ->first();

            if (!$existing) {
                AnswerOption::create([
                    'question_id' => $q->id,
                    'label'       => $label,
                    'value'       => $value,
                    'position'    => $position,
                    'score_map'   => $scoreMap,
                    'flags'       => $flags,
                ]);
            } else {
                $existing->update([
                    'position'  => $position,
                    'score_map' => $scoreMap,
                    'flags'     => $flags,
                ]);
            }
        }
    }
}

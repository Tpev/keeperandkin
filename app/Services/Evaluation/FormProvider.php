<?php

namespace App\Services\Evaluation;

use App\Models\EvaluationForm;

class FormProvider
{
    /**
     * Return the active form for a team (or global) as a simple array DTO.
     * Phase 0: safe skeleton. You can call this when wiring the UI later.
     */
    public function activeFormForTeam(?int $teamId = null): array
    {
        if (!config('kk.features.db_questions')) {
            // Legacy fallback (do NOT remove your current in-code question source).
            // Return a minimal, empty DTO for now; when you wire the UI, map your legacy array here.
            return [
                'form'     => null,
                'sections' => [], // Fill with legacy sections/questions when you flip the switch
            ];
        }

        // Phase 1 DB path (no seeding yet → might be null):
        $form = EvaluationForm::query()
            ->where('team_id', $teamId)
            ->orWhereNull('team_id')
            ->where('is_active', true)
            ->orderByRaw('team_id is null') // prefer team-specific over global if both exist
            ->latest('version')
            ->with([
                'sections' => fn ($q) => $q->orderBy('position'),
                'formQuestions' => fn ($q) => $q->orderBy('position')->with(['question', 'section', 'question.answerOptions' => fn($r) => $r->orderBy('position')]),
            ])
            ->first();

        if (!$form) {
            return ['form' => null, 'sections' => []];
        }

        // Build a UI-friendly DTO (sections with their questions & options)
        $sections = $form->sections->map(function ($section) use ($form) {
            $qs = $form->formQuestions
                ->where('section_id', $section->id)
                ->values()
                ->map(function ($fq) {
                    $q = $fq->question;
                    return [
                        'id'         => $q->id,
                        'type'       => $q->type,
                        'prompt'     => $q->prompt,
                        'help_text'  => $q->help_text,
                        'required'   => (bool) $fq->required,
                        'category'   => $q->category, // comfort_confidence | sociability | trainability | general
                        'visibility' => $fq->visibility, // always | staff_only | public_summary
                        'meta'       => $q->meta ?? [],
                        'options'    => $q->answerOptions->map(fn ($opt) => [
                            'id'        => $opt->id,
                            'label'     => $opt->label,
                            'value'     => $opt->value,
                            'score_map' => $opt->score_map ?? [],
                            'flags'     => $opt->flags ?? [],
                        ])->toArray(),
                    ];
                });

            return [
                'id'        => $section->id,
                'title'     => $section->title,
                'position'  => $section->position,
                'questions' => $qs->toArray(),
            ];
        })->toArray();

        return [
            'form'     => ['id' => $form->id, 'name' => $form->name, 'version' => $form->version],
            'sections' => $sections,
        ];
    }
}

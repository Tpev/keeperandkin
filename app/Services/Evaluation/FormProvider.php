<?php

namespace App\Services\Evaluation;

use App\Models\EvaluationForm;

class FormProvider
{
    /**
     * Return the active form (team-specific first, then global) as a simple array DTO.
     * Includes follow-up metadata so the UI can gate child questions.
     */
    public function activeFormForTeam(?int $teamId = null): array
    {
        // Legacy/feature-flag fallback
        if (!config('kk.features.db_questions')) {
            return [
                'form'     => null,
                'sections' => [],
            ];
        }

        /** @var \App\Models\EvaluationForm|null $form */
        $form = EvaluationForm::query()
            ->where('is_active', true)
            ->where(function ($q) use ($teamId) {
                // Prefer a team-scoped form if it exists; otherwise allow global (null team_id)
                $q->where('team_id', $teamId)->orWhereNull('team_id');
            })
            // Sort so a team form beats a global one (false < true), then most recent version
            ->orderByRaw('team_id is null asc')
            ->latest('version')
            ->with([
                'sections' => fn ($q) => $q->orderBy('position'),
                'formQuestions' => fn ($q) => $q
                    ->orderBy('position')
                    ->with([
                        'question:id,slug,prompt,help_text,type,category,meta',
                        'question.answerOptions' => fn ($r) => $r
                            ->orderBy('position')
                            ->select('id', 'question_id', 'label', 'value', 'position', 'score_map', 'flags'),
                        'section:id,form_id,title,position',
                        'followUpRule', // IMPORTANT for follow-up gating
                    ]),
            ])
            ->first();

        if (!$form) {
            return ['form' => null, 'sections' => []];
        }

        // Build a UI-friendly DTO grouped by sections with ordered questions
        $sections = $form->sections->map(function ($section) use ($form) {
            $fqs = $form->formQuestions
                ->where('section_id', $section->id)
                ->sortBy('position')
                ->values();

            $qDtos = $fqs->map(function ($fq) {
                $q = $fq->question;

                // Answer options DTO
                $opts = $q->answerOptions->map(static function ($o) {
                    return [
                        'id'        => (int) $o->id,
                        'label'     => $o->label,
                        'value'     => $o->value,
                        'position'  => (int) $o->position,
                        'score_map' => is_array($o->score_map) ? $o->score_map : (array) ($o->score_map ?? []),
                        'flags'     => is_array($o->flags) ? $o->flags : (array) ($o->flags ?? []),
                    ];
                })->values()->all();

                // Follow-up metadata DTO (if present)
                $follow = null;
                if ($fq->followUpRule) {
                    $follow = [
                        'parent_form_question_id' => (int) $fq->followUpRule->parent_form_question_id,
                        'trigger_option_ids'      => array_map('intval', (array) ($fq->followUpRule->trigger_option_ids ?? [])),
                        'display_mode'            => $fq->followUpRule->display_mode,  // e.g. inline_after_parent
                        'required_mode'           => $fq->followUpRule->required_mode, // e.g. visible_only|always
                    ];
                }

                return [
                    // IDs
                    'id'               => (int) $q->id,  // QUESTION id
                    'form_question_id' => (int) $fq->id, // FQ id (used to map to parent)
                    // Fields
                    'type'       => $q->type,
                    'prompt'     => $q->prompt,
                    'help_text'  => $q->help_text,
                    'required'   => (bool) $fq->required,
                    'category'   => $q->category,     // comfort_confidence|sociability|trainability|general
                    'visibility' => $fq->visibility,  // always|staff_only|public_summary
                    'meta'       => is_array($q->meta) ? $q->meta : (array) ($q->meta ?? []),
                    'options'    => $opts,
                    'follow_up'  => $follow,          // NULL or array as above
                ];
            })->values()->all();

            return [
                'id'        => (int) $section->id,
                'title'     => $section->title,
                'position'  => (int) $section->position,
                'questions' => $qDtos,
            ];
        })->values()->all();

        return [
            'form' => [
                'id'      => (int) $form->id,
                'name'    => $form->name,
                'version' => (int) $form->version,
            ],
            'sections' => $sections,
        ];
    }
}

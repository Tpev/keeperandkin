<?php

namespace App\Actions\Training;

use App\Models\Dog;
use App\Models\TrainingSession;
use App\Models\DogTrainingAssignment;
use Illuminate\Support\Facades\DB;

class GenerateDogTrainingPlan
{
    /**
     * Build/refresh the plan for the given dog from its latest evaluation selections.
     * - Collect selected AnswerOptions
     * - Resolve TrainingFlags (pivot) -> Sessions (pivot with ordering)
     * - Upsert dog_training_assignments (unique per dog+session)
     */
    public function handle(Dog $dog): int
    {
        $evaluation = $dog->latestEvaluation;
        if (!$evaluation) return 0;

        $responses = $evaluation->responses()
            ->with(['answerOption.trainingFlags.sessions', 'responseOptions.answerOption.trainingFlags.sessions'])
            ->get();

        $sessionRows = collect();

        foreach ($responses as $resp) {
            // single/boolean
            if ($resp->answerOption) {
                foreach ($resp->answerOption->trainingFlags as $flag) {
                    foreach ($flag->sessions as $sess) {
                        $sessionRows->push([
                            'session' => $sess,
                            'flag'    => $flag,
                        ]);
                    }
                }
            }
            // multi
            foreach ($resp->responseOptions as $ro) {
                $opt = $ro->answerOption;
                if (!$opt) continue;
                foreach ($opt->trainingFlags as $flag) {
                    foreach ($flag->sessions as $sess) {
                        $sessionRows->push([
                            'session' => $sess,
                            'flag'    => $flag,
                        ]);
                    }
                }
            }
        }

        $grouped = $sessionRows
            ->unique(fn ($row) => $row['session']->id) // avoid dup sessions
            ->values();

        $createdOrTouched = 0;

        DB::transaction(function () use ($dog, $evaluation, $grouped, &$createdOrTouched) {
            foreach ($grouped as $row) {
                /** @var \App\Models\TrainingSession $session */
                $session = $row['session'];
                $flag    = $row['flag'];

                $assignment = DogTrainingAssignment::firstOrCreate(
                    [
                        'dog_id'              => $dog->id,
                        'training_session_id' => $session->id,
                    ],
                    [
                        'training_flag_id' => $flag->id ?? null,
                        'evaluation_id'    => $evaluation->id,
                        'status'           => 'pending',
                    ]
                );

                // If exists but has no origin flag/eval, fill it in
                $dirty = false;
                if (!$assignment->training_flag_id && $flag) {
                    $assignment->training_flag_id = $flag->id;
                    $dirty = true;
                }
                if (!$assignment->evaluation_id) {
                    $assignment->evaluation_id = $evaluation->id;
                    $dirty = true;
                }
                if ($dirty) $assignment->save();

                $createdOrTouched++;
            }
        });

        return $createdOrTouched;
    }
}

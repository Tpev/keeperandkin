<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EvaluationSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $evaluationId
    ) {}
}

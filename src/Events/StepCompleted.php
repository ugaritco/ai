<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\Providers\TextProvider;
use Ugarit\Ai\Gateway\StepResponse;

class StepCompleted
{
    /**
     * @param  string  $model  The model the step was requested against, which the responding model reported in $response->meta may differ from.
     * @param  float  $time  Wall time spent in the provider call, in milliseconds.
     */
    public function __construct(
        public string $invocationId,
        public int $stepNumber,
        public Agent $agent,
        public TextProvider $provider,
        public string $model,
        public bool $isFinalStep,
        public StepResponse $response,
        public float $time,
    ) {}
}

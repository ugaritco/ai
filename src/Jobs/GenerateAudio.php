<?php

namespace Ugarit\Ai\Jobs;

use Heritage\Contracts\Queue\ShouldQueue;
use Heritage\Foundation\Queue\Queueable;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\PendingResponses\PendingAudioGeneration;
use Ugarit\Ai\Responses\AudioResponse;

class GenerateAudio implements ShouldQueue
{
    use Concerns\InvokesQueuedResponseCallbacks;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PendingAudioGeneration $pendingAudio,
        public Lab|array|string|null $provider = null,
        public ?string $model = null) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->withCallbacks(fn (): AudioResponse => $this->pendingAudio->generate(
            $this->provider,
            $this->model,
        ));
    }
}

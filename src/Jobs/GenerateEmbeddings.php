<?php

namespace Ugarit\Ai\Jobs;

use Heritage\Contracts\Queue\ShouldQueue;
use Heritage\Foundation\Queue\Queueable;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\PendingResponses\PendingEmbeddingsGeneration;
use Ugarit\Ai\Responses\EmbeddingsResponse;

class GenerateEmbeddings implements ShouldQueue
{
    use Concerns\InvokesQueuedResponseCallbacks;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PendingEmbeddingsGeneration $pendingEmbeddings,
        public Lab|array|string|null $provider = null,
        public ?string $model = null) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->withCallbacks(fn (): EmbeddingsResponse => $this->pendingEmbeddings->generate(
            $this->provider,
            $this->model,
        ));
    }
}

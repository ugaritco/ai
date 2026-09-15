<?php

namespace Ugarit\Ai\Jobs;

use Heritage\Contracts\Queue\ShouldQueue;
use Heritage\Foundation\Queue\Queueable;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\PendingResponses\PendingImageGeneration;
use Ugarit\Ai\Responses\ImageResponse;

class GenerateImage implements ShouldQueue
{
    use Concerns\InvokesQueuedResponseCallbacks;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PendingImageGeneration $pendingImage,
        public Lab|array|string|null $provider = null,
        public ?string $model = null) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->withCallbacks(fn (): ImageResponse => $this->pendingImage->generate(
            $this->provider,
            $this->model,
        ));
    }
}

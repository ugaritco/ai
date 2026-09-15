<?php

namespace Ugarit\Ai\Jobs;

use Heritage\Contracts\Queue\ShouldQueue;
use Heritage\Foundation\Queue\Queueable;
use Ugarit\Ai\Approvals\Decisions;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Responses\AgentResponse;

class InvokeAgent implements ShouldQueue
{
    use Concerns\InvokesQueuedResponseCallbacks;
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Agent $agent,
        public Decisions|string $prompt = '',
        public array $attachments = [],
        public Lab|array|string|null $provider = null,
        public ?string $model = null) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->withCallbacks(fn (): AgentResponse => $this->agent->prompt(
            $this->prompt, $this->attachments, $this->provider, $this->model
        ));
    }

    /**
     * Get the display name for the queued job.
     */
    public function displayName(): string
    {
        return $this->agent::class;
    }
}

<?php

namespace Tests\Fixtures\Providers;

use BadMethodCallException;
use Closure;
use Heritage\Contracts\Events\Dispatcher;
use Ugarit\Ai\Contracts\Gateway\StepTextGateway;
use Ugarit\Ai\Contracts\Providers\TextProvider;
use Ugarit\Ai\Gateway\TextGenerationLoop;
use Ugarit\Ai\Prompts\AgentPrompt;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Responses\AgentResponse;
use Ugarit\Ai\Responses\StreamableAgentResponse;

class FakeStreamingProvider extends Provider implements TextProvider
{
    public function __construct(
        protected array $config,
        protected Dispatcher $events,
        protected Closure $streamFactory,
    ) {}

    public function stream(AgentPrompt $prompt): StreamableAgentResponse
    {
        return ($this->streamFactory)($this, $prompt);
    }

    public function prompt(AgentPrompt $prompt): AgentResponse
    {
        throw new BadMethodCallException('FakeStreamingProvider::prompt is not implemented.');
    }

    public function useTextGateway(StepTextGateway $gateway): self
    {
        return $this;
    }

    public function textGenerationLoop(): TextGenerationLoop
    {
        throw new BadMethodCallException('FakeStreamingProvider::textGenerationLoop is not implemented.');
    }

    public function defaultTextModel(): string
    {
        return 'fake-model';
    }

    public function cheapestTextModel(): string
    {
        return 'fake-model';
    }

    public function smartestTextModel(): string
    {
        return 'fake-model';
    }
}

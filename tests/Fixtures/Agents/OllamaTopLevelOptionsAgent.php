<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasProviderOptions;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Promptable;

class OllamaTopLevelOptionsAgent implements Agent, HasProviderOptions
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }

    public function providerOptions(Lab|string $provider): array
    {
        $provider = is_string($provider) ? Lab::tryFrom($provider) : $provider;

        return match ($provider) {
            Lab::Ollama => [
                'format' => 'json',
                'keep_alive' => '10m',
                'logprobs' => true,
                'num_ctx' => 8192,
            ],
            default => [],
        };
    }
}

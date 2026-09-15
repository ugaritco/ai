<?php

namespace Tests\Fixtures\Agents;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Ugarit\Ai\Attributes\Strict;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasProviderOptions;
use Ugarit\Ai\Contracts\HasStructuredOutput;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Promptable;

#[Strict]
class OllamaStructuredProviderOptionsAgent implements Agent, HasProviderOptions, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return 'You are a helpful assistant that uses structured output and knows about periodic table element symbols.';
    }

    /**
     * Get the structured output's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'symbol' => $schema->string()->required(),
        ];
    }

    public function providerOptions(Lab|string $provider): array
    {
        $provider = is_string($provider) ? Lab::tryFrom($provider) : $provider;

        return match ($provider) {
            Lab::Ollama => [
                'format' => 'json',
            ],
            default => [],
        };
    }
}

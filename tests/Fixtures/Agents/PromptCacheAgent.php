<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasProviderOptions;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Promptable;
use Tests\Fixtures\Tools\FixedNumberGenerator;

class PromptCacheAgent implements Agent, HasProviderOptions, HasTools
{
    use Promptable;

    public function __construct(
        protected bool $withTools = true,
        protected array $options = [],
    ) {
        //
    }

    public function instructions(): string
    {
        return 'You are a helpful assistant that generates numbers.';
    }

    public function tools(): iterable
    {
        return $this->withTools ? [new FixedNumberGenerator] : [];
    }

    public function providerOptions(Lab|string $provider): array
    {
        return $this->options;
    }
}

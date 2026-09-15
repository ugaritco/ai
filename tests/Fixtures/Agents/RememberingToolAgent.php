<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Concerns\RemembersConversations;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasProviderOptions;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Promptable;
use Tests\Fixtures\Tools\FixedNumberGenerator;

class RememberingToolAgent implements Agent, HasProviderOptions, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function __construct(public array $extraProviderOptions = []) {}

    public function instructions(): string
    {
        return 'Call the FixedNumberGenerator tool before answering. Answer with one short sentence.';
    }

    public function tools(): iterable
    {
        return [new FixedNumberGenerator];
    }

    public function providerOptions(Lab|string $provider): array
    {
        return $this->extraProviderOptions;
    }
}

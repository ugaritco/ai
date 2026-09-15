<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\Tool;

class InvokingTool
{
    /**
     * @param  array<string, mixed>  $arguments
     */
    public function __construct(
        public string $invocationId,
        public string $toolInvocationId,
        public Agent $agent,
        public Tool $tool,
        public array $arguments,
    ) {}
}

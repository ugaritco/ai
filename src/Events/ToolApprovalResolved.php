<?php

namespace Ugarit\Ai\Events;

use Heritage\Support\Collection;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Responses\Data\ToolResult;

class ToolApprovalResolved
{
    /**
     * @param  Collection<int, ToolResult>  $toolResults
     */
    public function __construct(
        public string $invocationId,
        public Agent $agent,
        public Collection $toolResults,
        public ?string $conversationId = null,
        public ?object $conversationUser = null,
    ) {}
}

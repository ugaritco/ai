<?php

namespace Ugarit\Ai\Events;

use Heritage\Support\Collection;
use Ugarit\Ai\Approvals\PendingApproval;
use Ugarit\Ai\Contracts\Agent;

class ToolApprovalRequested
{
    /**
     * @param  Collection<int, PendingApproval>  $pendingApprovals
     */
    public function __construct(
        public string $invocationId,
        public Agent $agent,
        public Collection $pendingApprovals,
        public ?string $conversationId = null,
        public ?object $conversationUser = null,
    ) {}
}

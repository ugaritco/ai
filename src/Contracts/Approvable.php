<?php

namespace Ugarit\Ai\Contracts;

use Ugarit\Ai\Approvals\Approval;
use Ugarit\Ai\Tools\Request;

interface Approvable
{
    /**
     * Indicate that the tool requires approval before execution.
     */
    public function requireApproval(?string $reason = null): static;

    /**
     * Indicate that the tool may execute without approval.
     */
    public function withoutApproval(): static;

    /**
     * Determine whether the tool should request approval for the given request.
     */
    public function shouldRequestApproval(Request $request): ?Approval;
}

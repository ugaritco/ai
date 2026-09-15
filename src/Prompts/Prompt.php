<?php

namespace Ugarit\Ai\Prompts;

use Ugarit\Ai\Approvals\Decisions;
use Ugarit\Ai\Contracts\Providers\TextProvider;

abstract class Prompt
{
    public function __construct(
        public readonly string $prompt,
        public readonly TextProvider $provider,
        public readonly string $model,
        public readonly ?Decisions $approvalDecisions = null,
    ) {}

    /**
     * Determine whether the prompt has tool approval decisions.
     */
    public function hasApprovalDecisions(): bool
    {
        return $this->approvalDecisions !== null;
    }
}

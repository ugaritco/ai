<?php

namespace Ugarit\Ai\Gateway;

use Ugarit\Ai\Messages\Message;
use Ugarit\Ai\Responses\Data\ToolResult;

class ApprovalResumption
{
    /**
     * @param  Message[]  $messages
     * @param  Message[]  $newMessages
     * @param  array<int, ToolResult>  $results
     */
    public function __construct(
        public array $messages,
        public array $newMessages,
        public array $results,
        public bool $shouldContinue,
    ) {}
}

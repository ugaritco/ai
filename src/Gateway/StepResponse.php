<?php

namespace Ugarit\Ai\Gateway;

use Heritage\Contracts\Support\Arrayable;
use JsonSerializable;
use Ugarit\Ai\Approvals\PendingApproval;
use Ugarit\Ai\Responses\Concerns\HasRawResponse;
use Ugarit\Ai\Responses\Data\FinishReason;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\Data\ToolCall;
use Ugarit\Ai\Responses\Data\Usage;

class StepResponse implements Arrayable, JsonSerializable
{
    use HasRawResponse;

    /**
     * @param  ToolCall[]  $toolCalls
     * @param  array<string, mixed>|null  $structured
     * @param  array<int, array<string, mixed>>  $providerContentBlocks
     * @param  PendingApproval[]  $pendingApprovals
     */
    public function __construct(
        public string $text,
        public array $toolCalls,
        public FinishReason $finishReason,
        public Usage $usage,
        public Meta $meta,
        public ?array $structured = null,
        public ?string $continuationToken = null,
        public array $providerContentBlocks = [],
        public array $pendingApprovals = [],
    ) {}

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'structured' => $this->structured,
            'tool_calls' => array_map(fn (ToolCall $tc): array => $tc->toArray(), $this->toolCalls),
            'provider_content_blocks' => $this->providerContentBlocks,
            'finish_reason' => $this->finishReason->value,
            'usage' => $this->usage->toArray(),
            'meta' => $this->meta->toArray(),
            'continuation_token' => $this->continuationToken,
        ];
    }

    /**
     * Get the JSON serializable representation of the instance.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}

<?php

namespace Ugarit\Ai\Responses;

use Heritage\Support\Collection;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Streaming\Events\Citation;
use Ugarit\Ai\Streaming\Events\StreamEnd;
use Ugarit\Ai\Streaming\Events\StreamEvent;
use Ugarit\Ai\Streaming\Events\TextDelta;
use Ugarit\Ai\Streaming\Events\ToolApprovalRequest;
use Ugarit\Ai\Streaming\Events\ToolCall;
use Ugarit\Ai\Streaming\Events\ToolResult;

class StreamedAgentResponse extends AgentResponse
{
    /** @var Collection<int, StreamEvent> */
    public Collection $events;

    /**
     * @param  Collection<int, StreamEvent>  $events
     */
    public function __construct(string $invocationId, Collection $events, Meta $meta)
    {
        parent::__construct(
            $invocationId,
            TextDelta::combine($events),
            StreamEnd::combineUsage($events),
            $meta,
        );

        $this->withToolCallsAndResults(
            toolCalls: $events->whereInstanceOf(ToolCall::class)->map->toolCall,
            toolResults: $events->whereInstanceOf(ToolResult::class)->map->toolResult,
        );

        $this->events = $events;

        // A streamed run only ever sees its citations as events, not on the parsed body...
        $this->meta->citations = Citation::combine($events);

        $this->withPendingApprovals(
            $events->whereInstanceOf(ToolApprovalRequest::class)
                ->flatMap(fn (ToolApprovalRequest $event) => $event->pendingApprovals)
                ->values()
        );
    }

    /**
     * Get the raw provider replay state for the paused assistant turn, if any.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pausedProviderContentBlocks(): array
    {
        return $this->events->whereInstanceOf(ToolApprovalRequest::class)->last()?->providerContentBlocks ?? [];
    }
}

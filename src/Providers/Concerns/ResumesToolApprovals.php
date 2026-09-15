<?php

namespace Ugarit\Ai\Providers\Concerns;

use Closure;
use Heritage\Support\Collection;
use Ugarit\Ai\Ai;
use Ugarit\Ai\Approvals\Decision;
use Ugarit\Ai\Concerns\RemembersConversations;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\ConversationStore;
use Ugarit\Ai\Contracts\RemembersConversations as RemembersConversationsContract;
use Ugarit\Ai\Messages\AssistantMessage;
use Ugarit\Ai\Messages\Message;
use Ugarit\Ai\Models\Conversation;
use Ugarit\Ai\Prompts\AgentPrompt;

trait ResumesToolApprovals
{
    /**
     * Get the tool approval to resume with, unless the agent's gateway is faked.
     *
     * @return array<string, Decision>|null
     */
    protected function resumableApprovalFor(AgentPrompt $prompt): ?array
    {
        return $this->resumesAgainstRealGateway($prompt) ? $prompt->approvalDecisions->all() : null;
    }

    /**
     * Replace another provider's raw paused-turn replay state with its generic mapping, since raw blocks are only valid verbatim on the provider that produced them.
     *
     * @param  array<int, Message>  $messages
     * @return array<int, Message>
     */
    protected function withoutForeignProviderContentBlocks(array $messages): array
    {
        return array_map(function (Message $message): Message {
            if ($message instanceof AssistantMessage
                && filled($message->providerContentBlocks)
                && $message->providerContentBlocksProvider !== null
                && $message->providerContentBlocksProvider !== $this->name()) {
                return new AssistantMessage($message->content, $message->toolCalls);
            }

            return $message;
        }, $messages);
    }

    /**
     * Determine whether the prompt is a resume that runs tools against the real (non-faked) gateway.
     */
    protected function resumesAgainstRealGateway(AgentPrompt $prompt): bool
    {
        return $prompt->hasApprovalDecisions() && ! Ai::hasFakeGatewayFor($prompt->agent::class);
    }

    /**
     * Get a callback that captures a resume's resolved approval results, also durably recording them when the store supports it.
     */
    protected function approvalResultRecorderFor(AgentPrompt $prompt, ?Collection &$resolvedApprovalResults): ?Closure
    {
        if (! $this->resumesAgainstRealGateway($prompt)) {
            return null;
        }

        $storeRecorder = $this->storeApprovalResultRecorderFor($prompt);

        return function (array $toolResults) use ($storeRecorder, &$resolvedApprovalResults): void {
            $resolvedApprovalResults = collect($toolResults);

            if ($storeRecorder !== null) {
                $storeRecorder($toolResults);
            }
        };
    }

    /**
     * Get a callback that durably records resolved approval results before the run continues, if the store supports it.
     */
    protected function storeApprovalResultRecorderFor(AgentPrompt $prompt): ?Closure
    {
        $agent = $prompt->agent;

        if (! in_array(RemembersConversations::class, class_uses_recursive($agent), true)) {
            return null;
        }

        /** @var Agent&RemembersConversationsContract $agent */
        if ($agent->currentConversation() === null) {
            return null;
        }

        $store = app(ConversationStore::class);

        $conversationId = $agent->currentConversation();
        $participant = $agent->conversationParticipant();
        $participantType = $participant === null ? null : Conversation::participantType($participant);
        $participantId = $participant === null ? null : Conversation::participantKey($participant);

        return fn (array $toolResults) => $store->storeApprovalResults($conversationId, $participantType, $participantId, $toolResults);
    }
}

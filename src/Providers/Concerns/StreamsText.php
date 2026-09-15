<?php

namespace Ugarit\Ai\Providers\Concerns;

use Heritage\Support\Str;
use InvalidArgumentException;
use Ugarit\Ai\Contracts\Conversational;
use Ugarit\Ai\Contracts\HasStructuredOutput;
use Ugarit\Ai\Events\AgentStreamed;
use Ugarit\Ai\Events\StreamingAgent;
use Ugarit\Ai\Events\ToolApprovalRequested;
use Ugarit\Ai\Events\ToolApprovalResolved;
use Ugarit\Ai\Gateway\TextGenerationOptions;
use Ugarit\Ai\Messages\UserMessage;
use Ugarit\Ai\Prompts\AgentPrompt;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\StreamableAgentResponse;
use Ugarit\Ai\Responses\StreamedAgentResponse;
use Ugarit\Ai\Streaming\Events\ToolApprovalRequest;
use Throwable;

use function Ugarit\Ai\pipeline;

trait StreamsText
{
    use ResumesToolApprovals;

    /**
     * Stream the response from the given agent.
     */
    public function stream(AgentPrompt $prompt): StreamableAgentResponse
    {
        $invocationId = $prompt->invocationId ?? (string) Str::uuid7();

        // Held under its own name because the pipeline hands the middleware's prompt to the callback as $prompt...
        $originalPrompt = $prompt;

        $processedPrompt = null;
        $resolvedApprovalResults = null;

        try {
            $response = pipeline()
                ->send($prompt)
                ->through($this->gatherMiddlewareFor($prompt->agent))
                ->then(function (AgentPrompt $prompt) use ($invocationId, $originalPrompt, &$processedPrompt, &$resolvedApprovalResults): StreamableAgentResponse {
                    $processedPrompt = $prompt;

                    $agent = $prompt->agent;

                    if ($agent instanceof HasStructuredOutput) {
                        throw new InvalidArgumentException('Streaming structured output is not currently supported.');
                    }

                    $meta = new Meta($this->name(), $prompt->model);

                    $messages = $this->withoutForeignProviderContentBlocks([
                        ...($agent instanceof Conversational ? $agent->messages() : []),
                    ]);

                    if (! $prompt->hasApprovalDecisions()) {
                        $messages[] = new UserMessage($prompt->prompt, $prompt->attachments->all());
                    }

                    $tools = $this->resolveTools($agent);
                    $approval = $this->resumableApprovalFor($prompt);
                    $recordApprovalResults = $this->approvalResultRecorderFor($prompt, $resolvedApprovalResults);

                    // Validate eagerly so a mismatch throws before the stream begins, then thread the result into stream() so it isn't re-validated there...
                    $validatedApproval = $approval !== null
                        ? $this->textGenerationLoop()->validateApproval($approval, $messages, $tools)
                        : null;

                    $streamable = null;

                    // The response owns the "has anything reached the consumer" flag so this failure check and the caller's failover decision can never drift apart...
                    $streamable = new StreamableAgentResponse(
                        $invocationId,
                        function () use ($invocationId, $prompt, $originalPrompt, $agent, $messages, $tools, $approval, $recordApprovalResults, $validatedApproval, &$streamable) {
                            $this->events->dispatch(new StreamingAgent($invocationId, $prompt));

                            try {
                                foreach ($this->textGenerationLoop()->stream(
                                    $invocationId,
                                    $this,
                                    $prompt->model,
                                    (string) $agent->instructions(),
                                    $messages,
                                    $tools,
                                    null,
                                    TextGenerationOptions::forAgent($agent),
                                    $prompt->timeout,
                                    $approval,
                                    $recordApprovalResults,
                                    $validatedApproval,
                                    $this->runContextFor($invocationId, $prompt),
                                ) as $event) {
                                    if ($event instanceof ToolApprovalRequest) {
                                        $this->throwIfNotResumable($agent);
                                    }

                                    yield $event;
                                }
                            } catch (Throwable $exception) {
                                $this->recordAgentFailure($invocationId, $originalPrompt, $exception, $prompt, retryable: ! $streamable->hasYielded());

                                throw $exception;
                            }
                        },
                        $meta,
                    );

                    return $streamable;
                });
        } catch (Throwable $exception) {
            $this->recordAgentFailure($invocationId, $prompt, $exception, $processedPrompt);

            throw $exception;
        }

        return $response->then(function (StreamedAgentResponse $response) use ($invocationId, $prompt, &$processedPrompt, &$resolvedApprovalResults): void {
            $this->events->dispatch(
                new AgentStreamed($invocationId, $processedPrompt ?? $prompt, $response)
            );

            if ($response->hasPendingApprovals()) {
                $this->events->dispatch(new ToolApprovalRequested(
                    $invocationId,
                    $prompt->agent,
                    $response->pendingApprovals,
                    $response->conversationId,
                    $response->conversationUser,
                ));
            }

            if ($resolvedApprovalResults !== null) {
                $this->events->dispatch(new ToolApprovalResolved(
                    $invocationId,
                    $prompt->agent,
                    $resolvedApprovalResults,
                    $response->conversationId,
                    $response->conversationUser,
                ));
            }
        });
    }
}

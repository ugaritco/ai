<?php

namespace Ugarit\Ai\Providers\Concerns;

use Closure;
use Heritage\JsonSchema\JsonSchemaTypeFactory;
use Heritage\Support\Str;
use Ugarit\Ai\Ai;
use Ugarit\Ai\Concerns\RemembersConversations;
use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\Conversational;
use Ugarit\Ai\Contracts\ConversationStore;
use Ugarit\Ai\Contracts\HasMiddleware;
use Ugarit\Ai\Contracts\HasStructuredOutput;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Contracts\Tool;
use Ugarit\Ai\Events\AgentFailed;
use Ugarit\Ai\Events\AgentPrompted;
use Ugarit\Ai\Events\PromptingAgent;
use Ugarit\Ai\Events\ToolApprovalRequested;
use Ugarit\Ai\Events\ToolApprovalResolved;
use Ugarit\Ai\Exceptions\ApprovalNotResumableException;
use Ugarit\Ai\Exceptions\FailoverableException;
use Ugarit\Ai\Gateway\RunContext;
use Ugarit\Ai\Gateway\TextGenerationOptions;
use Ugarit\Ai\Messages\UserMessage;
use Ugarit\Ai\Middleware\RememberConversation;
use Ugarit\Ai\Prompts\AgentPrompt;
use Ugarit\Ai\Providers\Tools\ToolSearch;
use Ugarit\Ai\Responses\AgentResponse;
use Ugarit\Ai\Responses\StructuredAgentResponse;
use Ugarit\Ai\Responses\StructuredTextResponse;
use Ugarit\Ai\Responses\TextResponse;
use Ugarit\Ai\Tools\AgentTool;
use Ugarit\Ai\Tools\McpServerTool;
use Ugarit\Ai\Tools\McpTool;
use Throwable;

use function Ugarit\Ai\pipeline;

trait GeneratesText
{
    use ResumesToolApprovals;

    /**
     * Invoke the given agent.
     */
    public function prompt(AgentPrompt $prompt): AgentResponse
    {
        $invocationId = $prompt->invocationId ?? (string) Str::uuid7();

        $processedPrompt = null;
        $resolvedApprovalResults = null;

        try {
            $response = pipeline()
                ->send($prompt)
                ->through($this->gatherMiddlewareFor($prompt->agent))
                ->then(function (AgentPrompt $prompt) use ($invocationId, &$processedPrompt, &$resolvedApprovalResults): TextResponse {
                    $processedPrompt = $prompt;

                    $this->events->dispatch(new PromptingAgent($invocationId, $prompt));

                    $agent = $prompt->agent;

                    $messages = $this->withoutForeignProviderContentBlocks([
                        ...($agent instanceof Conversational ? $agent->messages() : []),
                    ]);

                    if (! $prompt->hasApprovalDecisions()) {
                        $messages[] = new UserMessage($prompt->prompt, $prompt->attachments->all());
                    }

                    $schema = $agent instanceof HasStructuredOutput ? $agent->schema(new JsonSchemaTypeFactory) : null;

                    $response = $this->textGenerationLoop()->generate(
                        $this,
                        $prompt->model,
                        (string) $agent->instructions(),
                        $messages,
                        $this->resolveTools($agent),
                        $schema,
                        TextGenerationOptions::forAgent($agent),
                        $prompt->timeout,
                        $this->resumableApprovalFor($prompt),
                        $this->approvalResultRecorderFor($prompt, $resolvedApprovalResults),
                        $this->runContextFor($invocationId, $prompt),
                    );

                    if ($response->hasPendingApprovals()) {
                        $this->throwIfNotResumable($agent);
                    }

                    $agentResponse = $response instanceof StructuredTextResponse
                        ? (new StructuredAgentResponse($invocationId, $response->structured, $response->text, $response->usage, $response->meta))
                            ->withMessages($response->messages)
                            ->withToolCallsAndResults($response->toolCalls, $response->toolResults)
                            ->withSteps($response->steps)
                            ->withRawResponse($response->raw)
                        : (new AgentResponse($invocationId, $response->text, $response->usage, $response->meta))
                            ->withMessages($response->messages)
                            ->withToolCallsAndResults($response->toolCalls, $response->toolResults)
                            ->withSteps($response->steps)
                            ->withRawResponse($response->raw);

                    $agentResponse->withPendingApprovals($response->pendingApprovals);

                    return $agentResponse;
                });
        } catch (Throwable $exception) {
            $this->recordAgentFailure($invocationId, $prompt, $exception, $processedPrompt);

            throw $exception;
        }

        $this->events->dispatch(
            new AgentPrompted($invocationId, $processedPrompt ?? $prompt, $response)
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

        return $response;
    }

    /**
     * Gather the middleware for the given agent.
     */
    protected function gatherMiddlewareFor(Agent $agent): array
    {
        $middleware = Ai::hasFakeGatewayFor($agent::class) ? [function (AgentPrompt $prompt, Closure $next) {
            Ai::recordPrompt($prompt);

            return $next($prompt);
        }] : [];

        if (in_array(RemembersConversations::class, class_uses_recursive($agent))) {
            $middleware[] = new RememberConversation(resolve(ConversationStore::class), $this);
        }

        return $agent instanceof HasMiddleware
            ? [...$middleware, ...$agent->middleware()]
            : $middleware;
    }

    /**
     * Resolve the tools for the given agent, wrapping any agent instances as tools.
     */
    protected function resolveTools(Agent $agent): array
    {
        if (! $agent instanceof HasTools) {
            return [];
        }

        return array_map(
            fn ($tool) => $this->resolveTool($tool),
            [...$agent->tools()],
        );
    }

    /**
     * Resolve a tool returned by the agent into a native tool instance when needed.
     */
    protected function resolveTool(mixed $tool): mixed
    {
        return match (true) {
            $tool instanceof Agent => new AgentTool($tool),
            $tool instanceof Tool => $tool,
            $tool instanceof ToolSearch => $tool->withTools(
                array_map(fn ($nested) => $this->resolveTool($nested), $tool->tools),
            ),
            McpTool::supports($tool) => new McpTool($tool),
            McpServerTool::supports($tool) => new McpServerTool($tool),
            default => $tool,
        };
    }

    /**
     * Build the context that identifies this run and reports its step and tool events.
     */
    protected function runContextFor(string $invocationId, AgentPrompt $prompt): RunContext
    {
        return new RunContext($invocationId, $prompt->agent, $this, $prompt->model, $this->events);
    }

    /**
     * Dispatch the terminal failure event for a run, unless the caller may still retry it against another provider.
     */
    protected function recordAgentFailure(string $invocationId, AgentPrompt $prompt, Throwable $exception, ?AgentPrompt $processedPrompt = null, bool $retryable = true): void
    {
        // A failoverable exception is only terminal once the caller has run out of providers to try...
        if ($retryable &&
            ! $prompt->isFinalAttempt() &&
            $exception instanceof FailoverableException) {
            return;
        }

        $this->events->dispatch(
            new AgentFailed($invocationId, $processedPrompt ?? $prompt, $exception)
        );
    }

    /**
     * Throw when a pause has surfaced on an agent that cannot resume it from persisted history.
     */
    protected function throwIfNotResumable(Agent $agent): void
    {
        if (! $this->agentCanResumeApprovals($agent)) {
            throw ApprovalNotResumableException::make();
        }
    }

    /**
     * Determine whether the given agent can resume a paused approval from persisted history.
     */
    protected function agentCanResumeApprovals(Agent $agent): bool
    {
        return $agent instanceof Conversational;
    }
}

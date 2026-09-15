<?php

namespace Ugarit\Ai\Contracts\Providers;

use Ugarit\Ai\Contracts\Gateway\StepTextGateway;
use Ugarit\Ai\Gateway\TextGenerationLoop;
use Ugarit\Ai\Prompts\AgentPrompt;
use Ugarit\Ai\Responses\AgentResponse;
use Ugarit\Ai\Responses\StreamableAgentResponse;

interface TextProvider extends Provider
{
    /**
     * Invoke the given agent.
     */
    public function prompt(AgentPrompt $prompt): AgentResponse;

    /**
     * Stream the response from the given agent.
     */
    public function stream(AgentPrompt $prompt): StreamableAgentResponse;

    /**
     * Set the provider's text gateway.
     */
    public function useTextGateway(StepTextGateway $gateway): self;

    /**
     * Get the multi-step text generation loop wrapping the provider's text gateway.
     */
    public function textGenerationLoop(): TextGenerationLoop;

    /**
     * Get the name of the default text model.
     */
    public function defaultTextModel(): string;

    /**
     * Get the name of the cheapest text model.
     */
    public function cheapestTextModel(): string;

    /**
     * Get the name of the smartest text model.
     */
    public function smartestTextModel(): string;
}

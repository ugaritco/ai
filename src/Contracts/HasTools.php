<?php

namespace Ugarit\Ai\Contracts;

use Ugarit\Ai\Providers\Tools\ProviderTool;

interface HasTools
{
    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable;
}

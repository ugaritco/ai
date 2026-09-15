<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Tests\Fixtures\Tools\ApprovableNumberGenerator;
use Tests\Fixtures\Tools\SideEffectRecorder;

class StatelessMixedToolsAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You record side effects and generate numbers on request.';
    }

    /**
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [new SideEffectRecorder, new ApprovableNumberGenerator];
    }
}

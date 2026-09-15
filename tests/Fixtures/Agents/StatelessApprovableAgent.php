<?php

namespace Tests\Fixtures\Agents;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Contracts\HasTools;
use Ugarit\Ai\Promptable;
use Tests\Fixtures\Tools\ApprovableNumberGenerator;

class StatelessApprovableAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You generate numbers on request.';
    }

    /**
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [new ApprovableNumberGenerator];
    }
}

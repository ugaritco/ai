<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Contracts\Agent;
use Ugarit\Ai\Exceptions\FailoverableException;
use Ugarit\Ai\Providers\Provider;

class AgentFailedOver extends ProviderFailedOver
{
    public function __construct(
        public string $invocationId,
        public Agent $agent,
        public Provider $provider,
        public string $model,
        public FailoverableException $exception) {}
}

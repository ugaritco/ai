<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Exceptions\FailoverableException;
use Ugarit\Ai\Providers\Provider;

class ProviderFailedOver
{
    public function __construct(
        public Provider $provider,
        public string $model,
        public FailoverableException $exception) {}
}

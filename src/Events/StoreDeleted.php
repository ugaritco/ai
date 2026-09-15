<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Providers\Provider;

class StoreDeleted
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $storeId,
    ) {}
}

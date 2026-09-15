<?php

namespace Ugarit\Ai\Events;

use DateInterval;
use Heritage\Support\Collection;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Store;

class StoreCreated
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $name,
        public ?string $description,
        public Collection $fileIds,
        public ?DateInterval $expiresWhenIdleFor,
        public Store $store,
    ) {}
}

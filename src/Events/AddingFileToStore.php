<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Providers\Provider;

class AddingFileToStore
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $storeId,
        public string $fileId,
    ) {}
}

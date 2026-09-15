<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Providers\Provider;

class FileAddedToStore
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public string $storeId,
        public string $fileId,
        public string $documentId,
    ) {}
}

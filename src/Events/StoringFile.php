<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Providers\Provider;

class StoringFile
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public StorableFile $file,
    ) {}
}

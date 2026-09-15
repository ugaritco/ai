<?php

namespace Ugarit\Ai\Events;

use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Responses\StoredFileResponse;

class FileStored
{
    public function __construct(
        public string $invocationId,
        public Provider $provider,
        public StorableFile $file,
        public StoredFileResponse $response,
    ) {}
}

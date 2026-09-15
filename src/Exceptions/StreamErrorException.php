<?php

namespace Ugarit\Ai\Exceptions;

use Ugarit\Ai\Streaming\Events\Error;

class StreamErrorException extends AiException
{
    public function __construct(public readonly ?Error $error = null)
    {
        parent::__construct($error?->message ?? 'The provider ended the stream without completing the step.');
    }
}

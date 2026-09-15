<?php

namespace Ugarit\Ai\Contracts;

use Ugarit\Ai\Messages\Message;

interface Conversational
{
    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable;
}

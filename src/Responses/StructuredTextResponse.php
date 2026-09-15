<?php

namespace Ugarit\Ai\Responses;

use ArrayAccess;
use Heritage\Support\Collection;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\Data\Usage;

class StructuredTextResponse extends TextResponse implements ArrayAccess
{
    use ProvidesStructuredResponse;

    public function __construct(array $structured, string $text, public Usage $usage, public Meta $meta)
    {
        parent::__construct($text, $usage, $meta);

        $this->structured = $structured;
        $this->toolCalls = new Collection;
        $this->toolResults = new Collection;
    }

    /**
     * Get the string representation of the object.
     */
    #[\Override]
    public function __toString(): string
    {
        return (string) json_encode($this->structured);
    }
}

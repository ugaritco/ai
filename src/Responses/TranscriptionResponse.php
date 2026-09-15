<?php

namespace Ugarit\Ai\Responses;

use Heritage\Support\Collection;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\Data\TranscriptionSegment;
use Ugarit\Ai\Responses\Data\Usage;

class TranscriptionResponse implements \Stringable
{
    public string $text;

    /** @var Collection<int, TranscriptionSegment> */
    public Collection $segments;

    public Usage $usage;

    public Meta $meta;

    /**
     * @param  Collection<int, TranscriptionSegment>  $segments
     */
    public function __construct(
        string $text,
        Collection $segments,
        Usage $usage,
        Meta $meta,
    ) {
        $this->text = $text;
        $this->segments = $segments;
        $this->usage = $usage;
        $this->meta = $meta;
    }

    /**
     * Get the string representation of the transcription.
     */
    public function __toString(): string
    {
        return $this->text;
    }
}

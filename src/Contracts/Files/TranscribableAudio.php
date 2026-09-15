<?php

namespace Ugarit\Ai\Contracts\Files;

use Ugarit\Ai\PendingResponses\PendingTranscriptionGeneration;
use Stringable;

interface TranscribableAudio extends HasContent, HasMimeType, Stringable
{
    /**
     * Generate a transcription of the given audio.
     */
    public function transcription(): PendingTranscriptionGeneration;
}

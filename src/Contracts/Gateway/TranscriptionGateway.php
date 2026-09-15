<?php

namespace Ugarit\Ai\Contracts\Gateway;

use Ugarit\Ai\Contracts\Files\TranscribableAudio;
use Ugarit\Ai\Contracts\Providers\TranscriptionProvider;
use Ugarit\Ai\Responses\TranscriptionResponse;

interface TranscriptionGateway
{
    /**
     * Generate text from the given audio.
     *
     * @param  array<string, mixed>  $providerOptions
     */
    public function generateTranscription(
        TranscriptionProvider $provider,
        string $model,
        TranscribableAudio $audio,
        ?string $language = null,
        bool $diarize = false,
        int $timeout = 30,
        array $providerOptions = [],
    ): TranscriptionResponse;
}

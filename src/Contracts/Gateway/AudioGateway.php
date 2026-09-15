<?php

namespace Ugarit\Ai\Contracts\Gateway;

use Ugarit\Ai\Contracts\Providers\AudioProvider;
use Ugarit\Ai\Responses\AudioResponse;

interface AudioGateway
{
    /**
     * Generate audio from the given text.
     */
    public function generateAudio(
        AudioProvider $provider,
        string $model,
        string $text,
        string $voice,
        ?string $instructions = null,
        int $timeout = 30,
    ): AudioResponse;
}

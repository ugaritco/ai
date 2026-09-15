<?php

namespace Ugarit\Ai\Contracts\Providers;

use Ugarit\Ai\Contracts\Files\TranscribableAudio;
use Ugarit\Ai\Contracts\Gateway\TranscriptionGateway;
use Ugarit\Ai\Responses\TranscriptionResponse;

interface TranscriptionProvider extends Provider
{
    /**
     * Generate audio from the given text.
     *
     * @param  array<string, mixed>  $providerOptions
     */
    public function transcribe(
        TranscribableAudio $audio,
        ?string $language = null,
        bool $diarize = false,
        ?string $model = null,
        ?int $timeout = null,
        array $providerOptions = [],
    ): TranscriptionResponse;

    /**
     * Get the provider's transcription gateway.
     */
    public function transcriptionGateway(): TranscriptionGateway;

    /**
     * Set the provider's transcription gateway.
     */
    public function useTranscriptionGateway(TranscriptionGateway $gateway): self;

    /**
     * Get the name of the default transcription (STT) model.
     */
    public function defaultTranscriptionModel(): string;
}

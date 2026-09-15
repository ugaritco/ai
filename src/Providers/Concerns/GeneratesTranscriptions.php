<?php

namespace Ugarit\Ai\Providers\Concerns;

use Heritage\Support\Str;
use Ugarit\Ai\Ai;
use Ugarit\Ai\Contracts\Files\TranscribableAudio;
use Ugarit\Ai\Events\GeneratingTranscription;
use Ugarit\Ai\Events\TranscriptionGenerated;
use Ugarit\Ai\Prompts\TranscriptionPrompt;
use Ugarit\Ai\Responses\TranscriptionResponse;

trait GeneratesTranscriptions
{
    /**
     * Transcribe the given audio to text.
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
    ): TranscriptionResponse {
        $invocationId = (string) Str::uuid7();

        $model ??= $this->defaultTranscriptionModel();

        $prompt = new TranscriptionPrompt($audio, $language, $diarize, $this, $model, $timeout, $providerOptions);

        if (Ai::transcriptionsAreFaked()) {
            Ai::recordTranscriptionGeneration($prompt);
        }

        $this->events->dispatch(new GeneratingTranscription(
            $invocationId, $this, $model, $prompt,
        ));

        return tap($this->transcriptionGateway()->generateTranscription(
            $this, $model, $prompt->audio, $prompt->language, $prompt->diarize, $prompt->timeout ?? 30, $prompt->providerOptions
        ), function (TranscriptionResponse $response) use ($invocationId, $model, $prompt): void {
            $this->events->dispatch(new TranscriptionGenerated(
                $invocationId, $this, $model, $prompt, $response
            ));
        });
    }
}

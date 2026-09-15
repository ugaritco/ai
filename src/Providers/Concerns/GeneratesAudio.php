<?php

namespace Ugarit\Ai\Providers\Concerns;

use Heritage\Support\Str;
use Ugarit\Ai\Ai;
use Ugarit\Ai\Events\AudioGenerated;
use Ugarit\Ai\Events\GeneratingAudio;
use Ugarit\Ai\Prompts\AudioPrompt;
use Ugarit\Ai\Responses\AudioResponse;

trait GeneratesAudio
{
    /**
     * Generate audio from the given text.
     */
    public function audio(
        string $text,
        string $voice = 'default-female',
        ?string $instructions = null,
        ?string $model = null,
        int $timeout = 30,
    ): AudioResponse {
        $invocationId = (string) Str::uuid7();

        $model ??= $this->defaultAudioModel();

        $prompt = new AudioPrompt($text, $voice, $instructions, $this, $model, $timeout);

        if (Ai::audioIsFaked()) {
            Ai::recordAudioGeneration($prompt);
        }

        $this->events->dispatch(new GeneratingAudio(
            $invocationId, $this, $model, $prompt,
        ));

        return tap($this->audioGateway()->generateAudio(
            $this, $model, $prompt->text, $prompt->voice, $prompt->instructions, $timeout,
        ), function (AudioResponse $response) use ($invocationId, $model, $prompt): void {
            $this->events->dispatch(new AudioGenerated(
                $invocationId, $this, $model, $prompt, $response,
            ));
        });
    }
}

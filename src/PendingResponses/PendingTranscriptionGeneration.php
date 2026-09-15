<?php

namespace Ugarit\Ai\PendingResponses;

use Heritage\Support\Traits\Conditionable;
use Ugarit\Ai\Ai;
use Ugarit\Ai\Contracts\Files\TranscribableAudio;
use Ugarit\Ai\Enums\Lab;
use Ugarit\Ai\Events\ProviderFailedOver;
use Ugarit\Ai\Exceptions\FailoverableException;
use Ugarit\Ai\Files\LocalAudio;
use Ugarit\Ai\Files\StoredAudio;
use Ugarit\Ai\Jobs\GenerateTranscription;
use Ugarit\Ai\PendingResponses\Concerns\ResolvesProviderOptions;
use Ugarit\Ai\Prompts\QueuedTranscriptionPrompt;
use Ugarit\Ai\Providers\Provider;
use Ugarit\Ai\Responses\QueuedTranscriptionResponse;
use Ugarit\Ai\Responses\TranscriptionResponse;
use LogicException;

class PendingTranscriptionGeneration
{
    use Conditionable;
    use ResolvesProviderOptions;

    protected ?string $language = null;

    protected bool $diarize = false;

    protected int $timeout = 30;

    public function __construct(
        protected TranscribableAudio $audio,
    ) {}

    /**
     * Specify the language (ISO-639-1) of the audio being transcribed.
     */
    public function language(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Indicate that the transcript should be diarized.
     */
    public function diarize(bool $diarize = true): self
    {
        $this->diarize = $diarize;

        return $this;
    }

    /**
     * Specify the timeout (in seconds) for the transcription generation.
     */
    public function timeout(int $seconds = 30): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    /**
     * Generate the transcription.
     *
     * @throws FailoverableException if every configured provider fails to generate the transcription.
     */
    public function generate(Lab|array|string|null $provider = null, ?string $model = null): TranscriptionResponse
    {
        $providers = Provider::formatProviderAndModelList(
            $provider ?? config('ai.default_for_transcription'), $model
        );

        $lastException = null;

        foreach ($providers as $provider => $model) {
            $provider = Ai::fakeableTranscriptionProvider($provider);

            $model ??= $provider->defaultTranscriptionModel();

            $providerOptions = $this->resolveProviderOptions($provider);

            try {
                return $provider->transcribe($this->audio, $this->language, $this->diarize, $model, $this->timeout, $providerOptions);
            } catch (FailoverableException $e) {
                $lastException = $e;

                event(new ProviderFailedOver($provider, $model, $e));

                continue;
            }
        }

        throw $lastException;
    }

    /**
     * Queue the generation of the transcription.
     *
     * @throws LogicException if the audio attachment is not a local audio or an audio file stored on a filesystem disk.
     */
    public function queue(Lab|array|string|null $provider = null, ?string $model = null): QueuedTranscriptionResponse
    {
        if (! $this->audio instanceof StoredAudio &&
            ! $this->audio instanceof LocalAudio) {
            throw new LogicException('Only local audio or audio stored on a filesystem disk may be attachments for queued transcription generations.');
        }

        if (Ai::transcriptionsAreFaked()) {
            Ai::recordTranscriptionGeneration(
                new QueuedTranscriptionPrompt(
                    $this->audio,
                    $this->language,
                    $this->diarize,
                    $provider,
                    $model,
                    is_array($this->providerOptions) ? $this->providerOptions : [],
                )
            );
        }

        return new QueuedTranscriptionResponse(
            GenerateTranscription::dispatch($this, $provider, $model),
        );
    }
}

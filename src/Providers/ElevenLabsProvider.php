<?php

namespace Ugarit\Ai\Providers;

use Heritage\Contracts\Events\Dispatcher;
use Ugarit\Ai\Contracts\Gateway\AudioGateway;
use Ugarit\Ai\Contracts\Gateway\TranscriptionGateway;
use Ugarit\Ai\Contracts\Providers\AudioProvider;
use Ugarit\Ai\Contracts\Providers\TranscriptionProvider;
use Ugarit\Ai\Gateway\ElevenLabsGateway;

class ElevenLabsProvider extends Provider implements AudioProvider, TranscriptionProvider
{
    use Concerns\GeneratesAudio;
    use Concerns\GeneratesTranscriptions;
    use Concerns\HasAudioGateway;
    use Concerns\HasTranscriptionGateway;

    public function __construct(
        protected array $config,
        protected Dispatcher $events) {}

    /**
     * Get the provider's audio gateway.
     */
    public function audioGateway(): AudioGateway
    {
        return $this->audioGateway ??= new ElevenLabsGateway;
    }

    /**
     * Get the provider's transcription gateway.
     */
    public function transcriptionGateway(): TranscriptionGateway
    {
        return $this->transcriptionGateway ??= new ElevenLabsGateway;
    }

    /**
     * Get the name of the default audio (TTS) model.
     */
    public function defaultAudioModel(): string
    {
        return $this->config['models']['audio']['default'] ?? 'eleven_multilingual_v2';
    }

    /**
     * Get the name of the default transcription (STT) model.
     */
    public function defaultTranscriptionModel(): string
    {
        return $this->config['models']['transcription']['default'] ?? 'scribe_v2';
    }
}

<?php

namespace Ugarit\Ai\Files;

use Heritage\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;
use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Contracts\Files\TranscribableAudio;
use Ugarit\Ai\Files\Concerns\CanBeUploadedToProvider;
use Ugarit\Ai\Files\Concerns\HasRemoteContent;
use Ugarit\Ai\PendingResponses\PendingTranscriptionGeneration;
use Ugarit\Ai\Transcription;

class RemoteAudio extends Audio implements Arrayable, JsonSerializable, StorableFile, TranscribableAudio
{
    use CanBeUploadedToProvider;
    use HasRemoteContent;

    public function __construct(public string $url, ?string $mimeType = null)
    {
        if (blank($url)) {
            throw new InvalidArgumentException('Remote audio URL cannot be empty.');
        }

        $this->mime = $mimeType;
    }

    /**
     * Generate a transcription of the given audio.
     */
    public function transcription(): PendingTranscriptionGeneration
    {
        return Transcription::of($this);
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'type' => 'remote-audio',
            'name' => $this->name(),
            'url' => $this->url,
            'mime' => $this->mime,
        ];
    }

    /**
     * Get the JSON serializable representation of the instance.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}

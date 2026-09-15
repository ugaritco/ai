<?php

namespace Ugarit\Ai\Files;

use Heritage\Contracts\Support\Arrayable;
use Heritage\Filesystem\FilesystemAdapter;
use Heritage\Support\Facades\Storage;
use InvalidArgumentException;
use JsonSerializable;
use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Contracts\Files\TranscribableAudio;
use Ugarit\Ai\Files\Concerns\CanBeUploadedToProvider;
use Ugarit\Ai\PendingResponses\PendingTranscriptionGeneration;
use Ugarit\Ai\Transcription;
use RuntimeException;

class StoredAudio extends Audio implements Arrayable, JsonSerializable, StorableFile, TranscribableAudio
{
    use CanBeUploadedToProvider;

    public function __construct(public string $path, public ?string $disk = null)
    {
        if (blank($path)) {
            throw new InvalidArgumentException('Audio file path cannot be empty.');
        }
    }

    /**
     * Get the raw representation of the file.
     *
     * @throws RuntimeException if the file does not exist on the configured disk.
     */
    public function content(): string
    {
        return Storage::disk($this->disk)->get($this->path) ??
            throw new RuntimeException('File ['.$this->path.'] does not exist on disk ['.$this->disk.'].');
    }

    /**
     * Get the displayable name of the file.
     */
    #[\Override]
    public function name(): ?string
    {
        return $this->name ?? basename($this->path);
    }

    /**
     * Get the file's MIME type.
     */
    #[\Override]
    public function mimeType(): ?string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        return $this->mime ?? $disk->mimeType($this->path);
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
            'type' => 'stored-audio',
            'name' => $this->name(),
            'path' => $this->path,
            'disk' => $this->disk ?? config('filesystems.default'),
        ];
    }

    /**
     * Get the JSON serializable representation of the instance.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->content();
    }
}

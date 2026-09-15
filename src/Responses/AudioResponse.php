<?php

namespace Ugarit\Ai\Responses;

use Heritage\Support\Str;
use Ugarit\Ai\Concerns\Storable;
use Ugarit\Ai\Responses\Data\Meta;

class AudioResponse implements \Stringable
{
    use Storable;

    public ?string $mime = null;

    /**
     * @param  string  $audio  The Base64 representation of the audio.
     */
    public function __construct(
        public string $audio,
        public Meta $meta,
        ?string $mimeType = null,
    ) {
        $this->mime = $mimeType;
    }

    /**
     * Get a default filename for the file.
     */
    protected function randomStorageName(): string
    {
        return once(fn (): string => Str::random(40).match ($this->mime) {
            'audio/wav', 'audio/x-wav' => '.wav',
            default => '.mp3',
        });
    }

    /**
     * Get the raw representation of the audio.
     */
    public function content(): string
    {
        return base64_decode($this->audio);
    }

    /**
     * Get the MIME type for the audio.
     */
    public function mimeType(): ?string
    {
        return $this->mime;
    }

    /**
     * Get the raw string content of the audio.
     */
    public function __toString(): string
    {
        return $this->content();
    }
}

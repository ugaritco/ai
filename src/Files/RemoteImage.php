<?php

namespace Ugarit\Ai\Files;

use Heritage\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;
use Ugarit\Ai\Contracts\Files\StorableFile;
use Ugarit\Ai\Files\Concerns\CanBeUploadedToProvider;
use Ugarit\Ai\Files\Concerns\HasRemoteContent;

class RemoteImage extends Image implements Arrayable, JsonSerializable, StorableFile
{
    use CanBeUploadedToProvider;
    use HasRemoteContent;

    public function __construct(public string $url, ?string $mimeType = null)
    {
        if (blank($url)) {
            throw new InvalidArgumentException('Remote image URL cannot be empty.');
        }

        $this->mime = $mimeType;
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'type' => 'remote-image',
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

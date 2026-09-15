<?php

namespace Ugarit\Ai\Files;

use Heritage\Contracts\Support\Arrayable;
use JsonSerializable;
use Ugarit\Ai\Contracts\Files\HasProviderId;
use Ugarit\Ai\Files\Concerns\CanBeRetrievedOrDeletedFromProvider;

class ProviderImage extends Image implements Arrayable, HasProviderId, JsonSerializable
{
    use CanBeRetrievedOrDeletedFromProvider;

    public function __construct(public string $id) {}

    /**
     * Get the provider ID for the stored file.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'type' => 'provider-image',
            'id' => $this->id,
            'name' => $this->name(),
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

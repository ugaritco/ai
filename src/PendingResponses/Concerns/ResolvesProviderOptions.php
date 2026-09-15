<?php

namespace Ugarit\Ai\PendingResponses\Concerns;

use Closure;
use Ugarit\Ai\Providers\Provider;
use Ugarit\SerializableClosure\SerializableClosure;

trait ResolvesProviderOptions
{
    /** @var array<string, mixed>|SerializableClosure */
    protected array|SerializableClosure $providerOptions = [];

    /**
     * Specify provider-specific options for the request.
     *
     * @param  array<string, mixed>|Closure(Provider): ?array<string, mixed>  $options
     */
    public function withProviderOptions(array|Closure $options): self
    {
        $this->providerOptions = $options instanceof Closure
            ? new SerializableClosure($options)
            : $options;

        return $this;
    }

    /**
     * Resolve provider options for the given provider.
     *
     * @return array<string, mixed>
     */
    protected function resolveProviderOptions(Provider $provider): array
    {
        if ($this->providerOptions instanceof SerializableClosure) {
            return ($this->providerOptions)($provider) ?: [];
        }

        return $this->providerOptions;
    }
}

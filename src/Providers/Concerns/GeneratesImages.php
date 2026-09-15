<?php

namespace Ugarit\Ai\Providers\Concerns;

use Heritage\Support\Str;
use Ugarit\Ai\Ai;
use Ugarit\Ai\Events\GeneratingImage;
use Ugarit\Ai\Events\ImageGenerated;
use Ugarit\Ai\Files\Image;
use Ugarit\Ai\Prompts\ImagePrompt;
use Ugarit\Ai\Responses\ImageResponse;

trait GeneratesImages
{
    /**
     * Generate an image.
     *
     * @param  array<Image>  $attachments
     * @param  'low'|'medium'|'high'|null  $quality
     */
    public function image(
        string $prompt,
        array $attachments = [],
        ?string $size = null,
        ?string $quality = null,
        ?string $model = null,
        ?int $timeout = null,
    ): ImageResponse {
        $invocationId = (string) Str::uuid7();

        $model ??= $this->defaultImageModel();

        $prompt = new ImagePrompt($prompt, $attachments, $size, $quality, $this, $model);

        if (Ai::imagesAreFaked()) {
            Ai::recordImageGeneration($prompt);
        }

        $this->events->dispatch(new GeneratingImage(
            $invocationId, $this, $model, $prompt,
        ));

        return tap($this->imageGateway()->generateImage(
            $this, $model, $prompt->prompt, $prompt->attachments->all(), $prompt->size, $prompt->quality, $timeout,
        ), function (ImageResponse $response) use ($invocationId, $prompt, $model): void {
            $this->events->dispatch(new ImageGenerated(
                $invocationId, $this, $model, $prompt, $response,
            ));
        });
    }
}

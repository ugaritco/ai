<?php

namespace Ugarit\Ai\Contracts\Gateway;

use Ugarit\Ai\Contracts\Providers\ImageProvider;
use Ugarit\Ai\Files\Image;
use Ugarit\Ai\Responses\ImageResponse;

interface ImageGateway
{
    /**
     * Generate an image.
     *
     * @param  array<Image>  $attachments
     * @param  'low'|'medium'|'high'|null  $quality
     */
    public function generateImage(
        ImageProvider $provider,
        string $model,
        string $prompt,
        array $attachments = [],
        ?string $size = null,
        ?string $quality = null,
        ?int $timeout = null,
    ): ImageResponse;
}

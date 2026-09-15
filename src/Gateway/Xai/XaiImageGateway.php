<?php

namespace Ugarit\Ai\Gateway\Xai;

use Heritage\Support\Collection;
use Ugarit\Ai\Contracts\Gateway\ImageGateway;
use Ugarit\Ai\Contracts\Providers\ImageProvider;
use Ugarit\Ai\Files\Image;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Responses\Data\GeneratedImage;
use Ugarit\Ai\Responses\Data\Meta;
use Ugarit\Ai\Responses\Data\Usage;
use Ugarit\Ai\Responses\ImageResponse;

class XaiImageGateway implements ImageGateway
{
    use Concerns\CreatesXaiClient;
    use HandlesFailoverErrors;

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
    ): ImageResponse {
        $options = $provider->defaultImageOptions($size, $quality);

        $response = $this->withErrorHandling(
            $provider->name(),
            fn () => $this->client($provider, $timeout ?? 120)
                ->post('images/generations', array_merge(array_filter([
                    'model' => $model,
                    'prompt' => $prompt,
                    'response_format' => 'b64_json',
                ]), $options))
        );

        $response = $response->json();

        return new ImageResponse(
            new Collection([
                new GeneratedImage($response['data'][0]['b64_json'], 'image/jpeg'),
            ]),
            new Usage,
            new Meta($provider->name(), $model),
        );
    }
}

<?php

namespace Ugarit\Ai\Gateway\Gemini\Concerns;

use Heritage\Http\UploadedFile;
use Heritage\Support\Collection;
use Heritage\Support\Facades\Storage;
use InvalidArgumentException;
use Ugarit\Ai\Files\Base64Audio;
use Ugarit\Ai\Files\Base64Document;
use Ugarit\Ai\Files\Base64Image;
use Ugarit\Ai\Files\Base64Video;
use Ugarit\Ai\Files\File;
use Ugarit\Ai\Files\LocalAudio;
use Ugarit\Ai\Files\LocalDocument;
use Ugarit\Ai\Files\LocalImage;
use Ugarit\Ai\Files\LocalVideo;
use Ugarit\Ai\Files\ProviderDocument;
use Ugarit\Ai\Files\ProviderImage;
use Ugarit\Ai\Files\RemoteAudio;
use Ugarit\Ai\Files\RemoteDocument;
use Ugarit\Ai\Files\RemoteImage;
use Ugarit\Ai\Files\RemoteVideo;
use Ugarit\Ai\Files\StoredAudio;
use Ugarit\Ai\Files\StoredDocument;
use Ugarit\Ai\Files\StoredImage;
use Ugarit\Ai\Files\StoredVideo;

trait MapsAttachments
{
    /**
     * Map the given Ugarit attachments to Gemini content parts.
     */
    protected function mapAttachments(Collection $attachments): array
    {
        return $attachments->map(fn ($attachment): array => $this->mapAttachment($attachment))->all();
    }

    /**
     * Map a Ugarit attachment to a Gemini content part.
     */
    protected function mapAttachment(mixed $attachment): array
    {
        if (! $attachment instanceof File && ! $attachment instanceof UploadedFile) {
            throw new InvalidArgumentException(
                'Unsupported attachment type ['.get_debug_type($attachment).']'
            );
        }

        return match (true) {
            $attachment instanceof ProviderImage => [
                'fileData' => [
                    'fileUri' => $attachment->id,
                ],
            ],
            $attachment instanceof Base64Image => [
                'inlineData' => [
                    'mimeType' => $attachment->mime,
                    'data' => $attachment->base64,
                ],
            ],
            $attachment instanceof RemoteImage => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'image/png',
                    'data' => base64_encode($attachment->content()),
                ],
            ],
            $attachment instanceof LocalImage => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'image/png',
                    'data' => base64_encode(file_get_contents($attachment->path)),
                ],
            ],
            $attachment instanceof StoredImage => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'image/png',
                    'data' => base64_encode(
                        (string) Storage::disk($attachment->disk)->get($attachment->path)
                    ),
                ],
            ],
            $attachment instanceof ProviderDocument => [
                'fileData' => [
                    'fileUri' => $attachment->id,
                ],
            ],
            $attachment instanceof Base64Document => [
                'inlineData' => [
                    'mimeType' => $attachment->mime,
                    'data' => $attachment->base64,
                ],
            ],
            $attachment instanceof LocalDocument => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'application/octet-stream',
                    'data' => base64_encode(file_get_contents($attachment->path)),
                ],
            ],
            $attachment instanceof RemoteDocument => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'application/octet-stream',
                    'data' => base64_encode($attachment->content()),
                ],
            ],
            $attachment instanceof StoredDocument => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'application/octet-stream',
                    'data' => base64_encode(
                        (string) Storage::disk($attachment->disk)->get($attachment->path)
                    ),
                ],
            ],
            $attachment instanceof Base64Audio => [
                'inlineData' => [
                    'mimeType' => $attachment->mime ?? 'audio/mp3',
                    'data' => $attachment->base64,
                ],
            ],
            $attachment instanceof LocalAudio => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'audio/mp3',
                    'data' => base64_encode(file_get_contents($attachment->path)),
                ],
            ],
            $attachment instanceof StoredAudio => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'audio/mp3',
                    'data' => base64_encode(
                        (string) Storage::disk($attachment->disk)->get($attachment->path)
                    ),
                ],
            ],
            $attachment instanceof RemoteAudio => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'audio/mp3',
                    'data' => base64_encode($attachment->content()),
                ],
            ],
            $attachment instanceof Base64Video => [
                'inlineData' => [
                    'mimeType' => $attachment->mime ?? 'video/mp4',
                    'data' => $attachment->base64,
                ],
            ],
            $attachment instanceof LocalVideo => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'video/mp4',
                    'data' => base64_encode(file_get_contents($attachment->path)),
                ],
            ],
            $attachment instanceof StoredVideo => [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'video/mp4',
                    'data' => base64_encode(
                        (string) Storage::disk($attachment->disk)->get($attachment->path)
                    ),
                ],
            ],
            $attachment instanceof RemoteVideo => $this->isYouTubeUrl($attachment->url) ? [
                'fileData' => array_filter([
                    'mimeType' => $attachment->mime,
                    'fileUri' => $attachment->url,
                ]),
            ] : [
                'inlineData' => [
                    'mimeType' => $attachment->mimeType() ?? 'video/mp4',
                    'data' => base64_encode($attachment->content()),
                ],
            ],
            $attachment instanceof UploadedFile => [
                'inlineData' => [
                    'mimeType' => $attachment->getClientMimeType(),
                    'data' => base64_encode($attachment->get()),
                ],
            ],
            default => throw new InvalidArgumentException('Unsupported attachment type ['.get_debug_type($attachment).']'),
        };
    }

    /**
     * Determine if the given URL is a YouTube URL, which Gemini accepts as a file URI.
     */
    protected function isYouTubeUrl(string $url): bool
    {
        return in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), [
            'youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be',
        ], true);
    }
}

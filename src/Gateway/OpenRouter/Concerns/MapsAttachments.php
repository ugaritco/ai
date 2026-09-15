<?php

namespace Ugarit\Ai\Gateway\OpenRouter\Concerns;

use Heritage\Http\UploadedFile;
use Heritage\Support\Collection;
use Heritage\Support\Facades\Storage;
use InvalidArgumentException;
use Ugarit\Ai\Files\Base64Document;
use Ugarit\Ai\Files\Base64Image;
use Ugarit\Ai\Files\File;
use Ugarit\Ai\Files\LocalDocument;
use Ugarit\Ai\Files\LocalImage;
use Ugarit\Ai\Files\RemoteDocument;
use Ugarit\Ai\Files\RemoteImage;
use Ugarit\Ai\Files\StoredDocument;
use Ugarit\Ai\Files\StoredImage;

trait MapsAttachments
{
    /**
     * Map the given Ugarit attachments to Chat Completions content parts.
     */
    protected function mapAttachments(Collection $attachments): array
    {
        return $attachments->map(function ($attachment): array {
            if (! $attachment instanceof File && ! $attachment instanceof UploadedFile) {
                throw new InvalidArgumentException(
                    'Unsupported attachment type ['.$attachment::class.']'
                );
            }

            return match (true) {
                $attachment instanceof Base64Image => [
                    'type' => 'image_url',
                    'image_url' => ['url' => 'data:'.$attachment->mime.';base64,'.$attachment->base64],
                ],
                $attachment instanceof RemoteImage => [
                    'type' => 'image_url',
                    'image_url' => ['url' => $attachment->url],
                ],
                $attachment instanceof LocalImage => [
                    'type' => 'image_url',
                    'image_url' => ['url' => 'data:'.($attachment->mimeType() ?? 'image/png').';base64,'.base64_encode(file_get_contents($attachment->path))],
                ],
                $attachment instanceof StoredImage => [
                    'type' => 'image_url',
                    'image_url' => ['url' => 'data:'.($attachment->mimeType() ?? 'image/png').';base64,'.base64_encode(
                        (string) Storage::disk($attachment->disk)->get($attachment->path)
                    )],
                ],
                $attachment instanceof Base64Document => [
                    'type' => 'file',
                    'file' => array_filter([
                        'filename' => $attachment->name(),
                        'file_data' => 'data:'.$attachment->mime.';base64,'.$attachment->base64,
                    ]),
                ],
                $attachment instanceof LocalDocument => [
                    'type' => 'file',
                    'file' => array_filter([
                        'filename' => $attachment->name(),
                        'file_data' => 'data:'.($attachment->mimeType() ?? 'application/octet-stream').';base64,'.base64_encode(file_get_contents($attachment->path)),
                    ]),
                ],
                $attachment instanceof RemoteDocument => [
                    'type' => 'file',
                    'file' => array_filter([
                        'filename' => $attachment->name(),
                        'file_data' => $attachment->url,
                    ]),
                ],
                $attachment instanceof StoredDocument => [
                    'type' => 'file',
                    'file' => array_filter([
                        'filename' => $attachment->name(),
                        'file_data' => 'data:'.($attachment->mimeType() ?? 'application/octet-stream').';base64,'.base64_encode(
                            (string) Storage::disk($attachment->disk)->get($attachment->path)
                        ),
                    ]),
                ],
                $attachment instanceof UploadedFile && $this->isImage($attachment) => [
                    'type' => 'image_url',
                    'image_url' => ['url' => 'data:'.$attachment->getClientMimeType().';base64,'.base64_encode($attachment->get())],
                ],
                $attachment instanceof UploadedFile => [
                    'type' => 'file',
                    'file' => [
                        'filename' => $attachment->getClientOriginalName(),
                        'file_data' => 'data:'.$attachment->getClientMimeType().';base64,'.base64_encode($attachment->get()),
                    ],
                ],
                default => throw new InvalidArgumentException('Unsupported attachment type ['.$attachment::class.']'),
            };
        })->all();
    }

    /**
     * Determine if the given uploaded file is an image.
     */
    protected function isImage(UploadedFile $attachment): bool
    {
        return in_array($attachment->getClientMimeType(), [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
            true);
    }
}

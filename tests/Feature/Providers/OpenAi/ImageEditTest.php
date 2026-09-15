<?php

use GuzzleHttp\Promise\PromiseInterface;
use Heritage\Http\Client\Request;
use Heritage\Http\UploadedFile;
use Heritage\Support\Facades\Http;
use Heritage\Support\Facades\Storage;
use Ugarit\Ai\Files\Base64Image;
use Ugarit\Ai\Files\LocalDocument;
use Ugarit\Ai\Files\LocalImage;
use Ugarit\Ai\Files\ProviderImage;
use Ugarit\Ai\Files\RemoteImage;
use Ugarit\Ai\Files\StoredImage;
use Ugarit\Ai\Image;

beforeEach(function (): void {
    config(['ai.providers.openai' => [
        ...config('ai.providers.openai'),
        'key' => 'test-key',
    ]]);
});

function fakeOpenAiImageEditResponse(): PromiseInterface
{
    return Http::response([
        'data' => [[
            'b64_json' => base64_encode('edited-image'),
        ]],
    ]);
}

test('a base64 image is sent to the edits endpoint as the image content', function (): void {
    Http::fake(['*' => fakeOpenAiImageEditResponse()]);

    Image::of('Make it brighter')
        ->attachments([new Base64Image(base64_encode('source-bytes'), 'image/png')])
        ->generate(provider: 'openai', model: 'gpt-image-1');

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), 'images/edits')
        && $request->hasFile('image[]', 'source-bytes', 'image.png'));
});

test('a remote image is fetched and sent as the image content', function (): void {
    Http::fake([
        'example.com/*' => Http::response('remote-bytes'),
        '*' => fakeOpenAiImageEditResponse(),
    ]);

    Image::of('Make it brighter')
        ->attachments([new RemoteImage('https://example.com/source.png', 'image/png')])
        ->generate(provider: 'openai', model: 'gpt-image-1');

    Http::assertSent(fn (Request $request): bool => $request->hasFile('image[]', 'remote-bytes'));
});

test('a local image is sent as the image content', function (): void {
    Http::fake(['*' => fakeOpenAiImageEditResponse()]);

    $path = tempnam(sys_get_temp_dir(), 'ai').'.png';
    file_put_contents($path, 'local-bytes');

    Image::of('Make it brighter')
        ->attachments([new LocalImage($path, 'image/png')])
        ->generate(provider: 'openai', model: 'gpt-image-1');

    Http::assertSent(fn (Request $request): bool => $request->hasFile('image[]', 'local-bytes'));

    unlink($path);
});

test('a stored image is sent as the image content', function (): void {
    Http::fake(['*' => fakeOpenAiImageEditResponse()]);

    Storage::fake('images');
    Storage::disk('images')->put('source.png', 'stored-bytes');

    Image::of('Make it brighter')
        ->attachments([new StoredImage('source.png', 'images')])
        ->generate(provider: 'openai', model: 'gpt-image-1');

    Http::assertSent(fn (Request $request): bool => $request->hasFile('image[]', 'stored-bytes'));
});

test('an uploaded file is sent as the image content', function (): void {
    Http::fake(['*' => fakeOpenAiImageEditResponse()]);

    $path = tempnam(sys_get_temp_dir(), 'ai').'.png';
    file_put_contents($path, 'uploaded-bytes');

    Image::of('Make it brighter')
        ->attachments([new UploadedFile($path, 'source.png', 'image/png', null, true)])
        ->generate(provider: 'openai', model: 'gpt-image-1');

    Http::assertSent(fn (Request $request): bool => $request->hasFile('image[]', 'uploaded-bytes'));

    unlink($path);
});

test('a provider image cannot be used for edits', function (): void {
    Http::fake(['*' => fakeOpenAiImageEditResponse()]);

    Image::of('Make it brighter')
        ->attachments([new ProviderImage('file-123')])
        ->generate(provider: 'openai', model: 'gpt-image-1');
})->throws(InvalidArgumentException::class, 'Unsupported image attachment type');

test('a document attachment cannot be used for edits', function (): void {
    Http::fake(['*' => fakeOpenAiImageEditResponse()]);

    Image::of('Make it brighter')
        ->attachments([new LocalDocument('/tmp/source.pdf', 'application/pdf')])
        ->generate(provider: 'openai', model: 'gpt-image-1');
})->throws(InvalidArgumentException::class, 'Unsupported image attachment type');

test('non gpt-image models send a single image field', function (): void {
    Http::fake(['*' => fakeOpenAiImageEditResponse()]);

    Image::of('Make it brighter')
        ->attachments([new Base64Image(base64_encode('source-bytes'), 'image/png')])
        ->generate(provider: 'openai', model: 'dall-e-2');

    Http::assertSent(fn (Request $request): bool => $request->hasFile('image', 'source-bytes', 'image.png'));
});

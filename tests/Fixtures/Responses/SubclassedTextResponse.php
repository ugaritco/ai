<?php

namespace Tests\Fixtures\Responses;

use Ugarit\Ai\Responses\TextResponse;

class SubclassedTextResponse extends TextResponse
{
    private string $secret = 'default';

    public function rememberSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function secret(): string
    {
        return $this->secret;
    }
}

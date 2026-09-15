<?php

namespace Tests\Fixtures\Responses;

use Ugarit\Ai\Responses\Data\Step;

class SubclassedStep extends Step
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

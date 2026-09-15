<?php

namespace Ugarit\Ai\Gateway\AzureOpenAi;

use Ugarit\Ai\Gateway\OpenAi\OpenAiStoreGateway;

class AzureOpenAiStoreGateway extends OpenAiStoreGateway
{
    use Concerns\CreatesAzureOpenAiClient;
}

<?php

namespace Ugarit\Ai\Gateway\DeepSeek;

use Heritage\Contracts\Events\Dispatcher;
use Ugarit\Ai\Contracts\Gateway\StepTextGateway;
use Ugarit\Ai\Gateway\Concerns\HandlesFailoverErrors;
use Ugarit\Ai\Gateway\Concerns\ParsesServerSentEvents;
use Ugarit\Ai\Gateway\OpenAiCompatible\Concerns\MapsChatCompletionTools;
use Ugarit\Ai\Gateway\OpenAiCompatible\Concerns\PerformsChatCompletionSteps;

class DeepSeekGateway implements StepTextGateway
{
    use Concerns\BuildsTextRequests;
    use Concerns\CreatesDeepSeekClient;
    use Concerns\HandlesTextStreaming;
    use Concerns\MapsAttachments;
    use Concerns\MapsMessages;
    use Concerns\ParsesTextResponses;
    use HandlesFailoverErrors;
    use MapsChatCompletionTools;
    use ParsesServerSentEvents;
    use PerformsChatCompletionSteps;

    public function __construct(protected Dispatcher $events)
    {
        //
    }
}

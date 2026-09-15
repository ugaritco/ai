<?php

namespace Tests\Fixtures\Tools;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Ugarit\Ai\Concerns\InteractsWithApprovals;
use Ugarit\Ai\Contracts\Approvable;
use Ugarit\Ai\Contracts\Tool;
use Ugarit\Ai\Tools\Request;

class ThrowingApprovableGenerator implements Approvable, Tool
{
    use InteractsWithApprovals;

    public function description(): string
    {
        return 'Generates a number, but requires human approval first and always fails.';
    }

    public function handle(Request $request): string
    {
        throw new \Exception('Forced to throw exception.');
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}

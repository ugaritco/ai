<?php

namespace Tests\Fixtures\Tools;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Ugarit\Ai\Approvals\Approval;
use Ugarit\Ai\Concerns\InteractsWithApprovals;
use Ugarit\Ai\Contracts\Approvable;
use Ugarit\Ai\Contracts\Tool;
use Ugarit\Ai\Tools\Request;

class ApprovableNumberGenerator implements Approvable, Tool
{
    use InteractsWithApprovals {
        shouldRequestApproval as protected traitShouldRequestApproval;
    }

    public static int $invocations = 0;

    public static int $approvalChecks = 0;

    public function description(): string
    {
        return 'Generates a number, but requires human approval first.';
    }

    public function handle(Request $request): string
    {
        static::$invocations++;

        return '72019';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function shouldRequestApproval(Request $request): ?Approval
    {
        static::$approvalChecks++;

        return $this->traitShouldRequestApproval($request);
    }
}

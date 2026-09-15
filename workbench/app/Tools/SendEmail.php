<?php

namespace Workbench\App\Tools;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Ugarit\Ai\Concerns\InteractsWithApprovals;
use Ugarit\Ai\Contracts\Approvable;
use Ugarit\Ai\Contracts\Tool;
use Ugarit\Ai\Tools\Request;

class SendEmail implements Approvable, Tool
{
    use InteractsWithApprovals;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): string
    {
        return 'Send an email on behalf of the user. Requires the user to approve the send.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): string
    {
        return 'Email sent to '.$request->string('to').' with subject ['.$request->string('subject').'].';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'to' => $schema->string()->description('The recipient email address')->required(),
            'subject' => $schema->string()->description('The email subject line')->required(),
            'body' => $schema->string()->description('The plain-text email body')->required(),
        ];
    }
}

<?php

namespace Tests\Fixtures\Mcp;

use Heritage\Contracts\JsonSchema\JsonSchema;
use Heritage\JsonSchema\Types\Type;
use Ugarit\Mcp\Request;
use Ugarit\Mcp\Response;
use Ugarit\Mcp\Server\Attributes\Description;
use Ugarit\Mcp\Server\Tool;

#[Description('Fetches the current weather for a city.')]
class FakeMcpServerTool extends Tool
{
    public array $invocations = [];

    public function handle(Request $request): Response
    {
        $this->invocations[] = $request->all();

        return Response::text('Sunny in '.$request->get('city').'.');
    }

    /**
     * @return array<string, Type>
     */
    #[\Override]
    public function schema(JsonSchema $schema): array
    {
        return [
            'city' => $schema->string()
                ->description('The city to get the weather for.')
                ->required(),
            'units' => $schema->string()
                ->enum(['celsius', 'fahrenheit'])
                ->default('celsius'),
        ];
    }
}

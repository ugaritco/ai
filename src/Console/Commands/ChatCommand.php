<?php

namespace Ugarit\Ai\Console\Commands;

use Heritage\Console\Attributes\Description;
use Heritage\Console\Attributes\Signature;
use Heritage\Console\Command;
use Ugarit\Ai\Responses\AgentResponse;
use Ugarit\Ai\Responses\StructuredAgentResponse;

use function Ugarit\Ai\agent;
use function Ugarit\Prompts\note;
use function Ugarit\Prompts\spin;
use function Ugarit\Prompts\textarea;

#[Description('Chat with one of your agents')]
#[Signature('agent:chat')]
class ChatCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $agent = agent(
            instructions: 'You are a helpful assistant.',
        );

        while (true) {
            $prompt = textarea('Prompt...');

            $response = spin(
                fn (): AgentResponse => $agent->prompt($prompt),
                message: 'Thinking...',
            );

            if ($response instanceof StructuredAgentResponse) {
                $response = json_encode($response->structured, JSON_PRETTY_PRINT);
            }

            note($response.PHP_EOL);
        }

        return Command::SUCCESS;
    }
}

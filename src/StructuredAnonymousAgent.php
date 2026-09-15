<?php

namespace Ugarit\Ai;

use Closure;
use Heritage\Contracts\JsonSchema\JsonSchema;
use Ugarit\Ai\Contracts\HasStructuredOutput;
use Ugarit\SerializableClosure\SerializableClosure;

class StructuredAnonymousAgent extends AnonymousAgent implements HasStructuredOutput
{
    /**
     * @var SerializableClosure|null
     */
    public $schema;

    public function __construct(
        public string $instructions,
        public iterable $messages,
        public iterable $tools,
        ?Closure $schema = null,
    ) {
        $this->schema = $schema instanceof Closure ? new SerializableClosure($schema) : null;
    }

    public function schema(JsonSchema $schema): array
    {
        return call_user_func($this->schema, $schema);
    }
}

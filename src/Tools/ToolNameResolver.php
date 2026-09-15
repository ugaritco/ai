<?php

namespace Ugarit\Ai\Tools;

use Ugarit\Ai\Contracts\Tool;

class ToolNameResolver
{
    public static function resolve(Tool $tool): string
    {
        return is_callable([$tool, 'name']) ? $tool->name() : class_basename($tool);
    }
}

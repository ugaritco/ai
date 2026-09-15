<?php

namespace Ugarit\Ai\Attributes;

use Attribute;
use Ugarit\Ai\Enums\Lab;

#[Attribute(Attribute::TARGET_CLASS)]
class Provider
{
    public function __construct(public Lab|array|string $value)
    {
        //
    }
}

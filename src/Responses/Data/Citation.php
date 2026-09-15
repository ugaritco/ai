<?php

namespace Ugarit\Ai\Responses\Data;

abstract class Citation
{
    public function __construct(
        public ?string $title = null,
    ) {}
}

<?php

namespace Ugarit\Ai\Contracts\Files;

use Stringable;

interface StorableFile extends HasContent, HasMimeType, HasName, Stringable
{
    //
}

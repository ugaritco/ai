<?php

namespace Ugarit\Ai\Concerns;

use Heritage\Database\Eloquent\Relations\MorphMany;
use Ugarit\Ai\Models\Conversation;

trait HasConversations
{
    /**
     * Get the conversations for the model.
     *
     * @return MorphMany<Conversation, $this>
     */
    public function conversations(): MorphMany
    {
        return $this->morphMany(Conversation::class, 'participant');
    }
}

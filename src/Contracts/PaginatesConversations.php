<?php

namespace Ugarit\Ai\Contracts;

use Heritage\Contracts\Pagination\CursorPaginator;
use Heritage\Pagination\Cursor;
use Ugarit\Ai\Storage\StoredMessage;

interface PaginatesConversations
{
    /**
     * Paginate the given conversation's messages, newest first.
     *
     * @return CursorPaginator<int, StoredMessage>
     */
    public function paginateConversationMessages(string $conversationId, int $perPage = 15, string $cursorName = 'cursor', Cursor|string|null $cursor = null): CursorPaginator;
}

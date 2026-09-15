<?php

namespace Ugarit\Ai\Exceptions;

use Heritage\Http\Request;
use Heritage\Support\Collection;
use Ugarit\Ai\Approvals\PendingApproval;
use Symfony\Component\HttpFoundation\Response;

class ApprovalMismatchException extends AiException
{
    /**
     * Create a new approval mismatch exception.
     *
     * @param  Collection<int, PendingApproval>  $pendingApprovals
     */
    public function __construct(string $message, public Collection $pendingApprovals)
    {
        parent::__construct($message);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response
    {
        return response()->json([
            'message' => $this->getMessage(),
            'approvals' => $this->pendingApprovals->values()->toArray(),
        ], 409);
    }
}

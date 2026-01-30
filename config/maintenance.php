<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Approval threshold (CLP)
    |--------------------------------------------------------------------------
    |
    | When closing a maintenance, if total_cost exceeds this amount, status
    | is set to "pending_approval" instead of "completed". A supervisor or
    | administrator must approve to mark it as completed.
    |
    */

    'approval_threshold' => (int) env('MAINTENANCE_APPROVAL_THRESHOLD', 500_000),

];

<?php

namespace App\Http\Controllers\Portal\Concerns;

use App\Models\Bill;
use App\Models\Lease;
use App\Models\Lessee;

trait EnsuresLesseeOwnership
{
    /**
     * Abort with a 404 unless the given lease belongs to the lessee.
     * Policies in this app are typed to `App\Models\User` and always deny
     * a `Lessee` principal, so portal controllers check ownership directly.
     */
    protected function ensureLesseeOwnsLease(Lease $lease, Lessee $lessee): void
    {
        abort_unless($lease->lessee_id === $lessee->id, 404);
    }

    /**
     * Abort with a 404 unless the given bill's lease belongs to the lessee.
     */
    protected function ensureLesseeOwnsBill(Bill $bill, Lessee $lessee): void
    {
        abort_unless($bill->lease->lessee_id === $lessee->id, 404);
    }
}

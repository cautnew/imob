<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Concerns\EnsuresLesseeOwnership;
use App\Models\Lease;
use App\Models\Lessee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaseController extends Controller
{
    use EnsuresLesseeOwnership;

    /**
     * List the authenticated lessee's own leases.
     */
    public function index(Request $request): Response
    {
        /** @var Lessee $lessee */
        $lessee = $request->user();

        return Inertia::render('portal/leases/index', [
            'leases' => $lessee->leases()
                ->with(['property:id,title,city,state', 'owner:id,name'])
                ->orderByDesc('start_date')
                ->get()
                ->map(fn (Lease $lease) => [
                    'id' => $lease->id,
                    'status' => $lease->status->value,
                    'status_label' => $lease->status->label(),
                    'start_date' => $lease->start_date->toDateString(),
                    'end_date' => $lease->end_date->toDateString(),
                    'rent_amount' => (string) $lease->rent_amount,
                    'property' => $lease->property->only('id', 'title', 'city', 'state'),
                ]),
        ]);
    }

    /**
     * Show a single lease owned by the authenticated lessee, read-only.
     */
    public function show(Request $request, Lease $lease): Response
    {
        /** @var Lessee $lessee */
        $lessee = $request->user();

        $this->ensureLesseeOwnsLease($lease, $lessee);

        $lease->load(['property:id,title,city,state', 'owner:id,name']);

        return Inertia::render('portal/leases/show', [
            'lease' => [
                'id' => $lease->id,
                'status' => $lease->status->value,
                'status_label' => $lease->status->label(),
                'start_date' => $lease->start_date->toDateString(),
                'end_date' => $lease->end_date->toDateString(),
                'rent_amount' => (string) $lease->rent_amount,
                'adjustment_index' => $lease->adjustment_index->value,
                'adjustment_interval_months' => $lease->adjustment_interval_months,
                'last_adjustment_date' => $lease->last_adjustment_date?->toDateString(),
                'renewal_type' => $lease->renewal_type->value,
                'notes' => $lease->notes,
                'property' => $lease->property,
                'owner' => $lease->owner,
            ],
            'events' => $lease->events()
                ->get(['id', 'type', 'occurred_on', 'description'])
                ->map(fn ($event) => [
                    'id' => $event->id,
                    'type' => $event->type->value,
                    'type_label' => $event->type->label(),
                    'occurred_on' => $event->occurred_on->toDateString(),
                    'description' => $event->description,
                ]),
        ]);
    }
}

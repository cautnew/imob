<?php

namespace App\Http\Controllers;

use App\Enums\LeaseAdjustmentIndex;
use App\Enums\LeaseEventType;
use App\Enums\LeaseRenewalType;
use App\Enums\LeaseStatus;
use App\Http\Requests\LeaseAdjustmentStoreRequest;
use App\Http\Requests\LeaseRenewalStoreRequest;
use App\Http\Requests\LeaseStatusUpdateRequest;
use App\Http\Requests\LeaseStoreRequest;
use App\Http\Requests\LeaseUpdateRequest;
use App\Models\Lease;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LeaseController extends Controller
{
    /**
     * List the leases of the authenticated user's company.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Lease::class);

        $status = $request->string('status')->value() ?: null;
        $search = $request->string('search')->value() ?: null;

        return Inertia::render('leases/index', [
            'leases' => $request->user()->company->leases()
                ->with(['property:id,title', 'owner:id,name', 'lessee:id,name'])
                ->when($status, fn ($query, $value) => $query->where('status', $value))
                ->when($search, fn ($query, $value) => $query
                    ->where(fn ($query) => $query
                        ->whereHas('property', fn ($query) => $query->where('title', 'like', "%{$value}%"))
                        ->orWhereHas('owner', fn ($query) => $query->where('name', 'like', "%{$value}%"))
                        ->orWhereHas('lessee', fn ($query) => $query->where('name', 'like', "%{$value}%"))))
                ->orderByDesc('start_date')
                ->paginate(15)
                ->withQueryString(),
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'statuses' => $this->statusOptions(),
        ]);
    }

    /**
     * Show the form to create a new lease.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', Lease::class);

        return Inertia::render('leases/create', $this->formOptions($request));
    }

    /**
     * Store a newly created lease.
     */
    public function store(LeaseStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $lease = DB::transaction(function () use ($validated): Lease {
            $lease = Lease::create($validated);

            $lease->property->owners()->syncWithoutDetaching([$lease->owner_id]);
            $lease->property->lessees()->syncWithoutDetaching([$lease->lessee_id]);

            $lease->events()->create([
                'type' => LeaseEventType::Created,
                'occurred_on' => $lease->start_date,
                'description' => sprintf(
                    'Contrato criado com aluguel de %s, vigência de %s a %s.',
                    $this->formatCurrency((float) $lease->rent_amount),
                    $lease->start_date->format('d/m/Y'),
                    $lease->end_date->format('d/m/Y'),
                ),
            ]);

            return $lease;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Locação criada com sucesso.')]);

        return to_route('leases.show', $lease);
    }

    /**
     * Show a lease's details and its contract timeline.
     */
    public function show(Lease $lease): Response
    {
        $this->authorize('view', $lease);

        $lease->load(['property:id,title,city,state', 'owner:id,name', 'lessee:id,name']);

        return Inertia::render('leases/show', [
            'lease' => [
                'id' => $lease->id,
                'start_date' => $lease->start_date->toDateString(),
                'end_date' => $lease->end_date->toDateString(),
                'rent_amount' => (string) $lease->rent_amount,
                'adjustment_index' => $lease->adjustment_index->value,
                'adjustment_interval_months' => $lease->adjustment_interval_months,
                'last_adjustment_date' => $lease->last_adjustment_date?->toDateString(),
                'renewal_type' => $lease->renewal_type->value,
                'status' => $lease->status->value,
                'notes' => $lease->notes,
                'property' => $lease->property,
                'owner' => $lease->owner,
                'lessee' => $lease->lessee,
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
            'statuses' => $this->statusOptions(),
            'adjustmentIndexes' => $this->adjustmentIndexOptions(),
            'renewalTypes' => $this->renewalTypeOptions(),
            'documents' => $lease->documents()
                ->get(['id', 'name', 'disk', 'path', 'original_filename', 'size', 'created_at'])
                ->map(fn ($document) => [
                    'id' => $document->id,
                    'name' => $document->name,
                    'url' => Storage::disk($document->disk)->url($document->path),
                    'original_filename' => $document->original_filename,
                    'size' => $document->size,
                    'created_at' => $document->created_at->toDateString(),
                ]),
        ]);
    }

    /**
     * Show the form to edit an existing lease.
     */
    public function edit(Request $request, Lease $lease): Response
    {
        $this->authorize('update', $lease);

        return Inertia::render('leases/edit', [
            'lease' => [
                'id' => $lease->id,
                'property_id' => $lease->property_id,
                'owner_id' => $lease->owner_id,
                'lessee_id' => $lease->lessee_id,
                'start_date' => $lease->start_date->toDateString(),
                'end_date' => $lease->end_date->toDateString(),
                'rent_amount' => (string) $lease->rent_amount,
                'adjustment_index' => $lease->adjustment_index->value,
                'adjustment_interval_months' => $lease->adjustment_interval_months,
                'renewal_type' => $lease->renewal_type->value,
                'notes' => $lease->notes,
            ],
            ...$this->formOptions($request),
        ]);
    }

    /**
     * Update an existing lease.
     */
    public function update(LeaseUpdateRequest $request, Lease $lease): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($lease, $validated): void {
            $lease->update($validated);

            $lease->property->owners()->syncWithoutDetaching([$lease->owner_id]);
            $lease->property->lessees()->syncWithoutDetaching([$lease->lessee_id]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Locação atualizada com sucesso.')]);

        return to_route('leases.show', $lease);
    }

    /**
     * Delete an existing lease.
     */
    public function destroy(Lease $lease): RedirectResponse
    {
        $this->authorize('delete', $lease);

        $lease->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Locação removida com sucesso.')]);

        return to_route('leases.index');
    }

    /**
     * Apply a rent adjustment (reajuste) to the lease and log it in the timeline.
     */
    public function storeAdjustment(LeaseAdjustmentStoreRequest $request, Lease $lease): RedirectResponse
    {
        $validated = $request->validated();
        $previousAmount = (float) $lease->rent_amount;

        DB::transaction(function () use ($lease, $validated, $previousAmount): void {
            $lease->update([
                'rent_amount' => $validated['rent_amount'],
                'last_adjustment_date' => $validated['effective_date'],
            ]);

            $lease->events()->create([
                'type' => LeaseEventType::Adjusted,
                'occurred_on' => $validated['effective_date'],
                'description' => trim(sprintf(
                    'Reajuste aplicado: aluguel alterado de %s para %s. %s',
                    $this->formatCurrency($previousAmount),
                    $this->formatCurrency((float) $validated['rent_amount']),
                    $validated['notes'] ?? '',
                )),
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reajuste registrado com sucesso.')]);

        return to_route('leases.show', $lease);
    }

    /**
     * Renew the lease, extending its end date and logging it in the timeline.
     */
    public function storeRenewal(LeaseRenewalStoreRequest $request, Lease $lease): RedirectResponse
    {
        $validated = $request->validated();
        $previousEndDate = $lease->end_date->format('d/m/Y');

        DB::transaction(function () use ($lease, $validated, $previousEndDate): void {
            $lease->update([
                'end_date' => $validated['end_date'],
                'status' => LeaseStatus::Active,
            ]);

            $lease->events()->create([
                'type' => LeaseEventType::Renewed,
                'occurred_on' => now(),
                'description' => trim(sprintf(
                    'Contrato renovado: vigência estendida de %s para %s. %s',
                    $previousEndDate,
                    $lease->end_date->format('d/m/Y'),
                    $validated['notes'] ?? '',
                )),
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Renovação registrada com sucesso.')]);

        return to_route('leases.show', $lease);
    }

    /**
     * Change the lease's situação and log it in the timeline.
     */
    public function updateStatus(LeaseStatusUpdateRequest $request, Lease $lease): RedirectResponse
    {
        $validated = $request->validated();
        $previousStatus = $lease->status;
        $newStatus = LeaseStatus::from($validated['status']);

        DB::transaction(function () use ($lease, $validated, $previousStatus, $newStatus): void {
            $lease->update(['status' => $newStatus]);

            $lease->events()->create([
                'type' => LeaseEventType::StatusChanged,
                'occurred_on' => now(),
                'description' => trim(sprintf(
                    'Situação alterada de "%s" para "%s". %s',
                    $previousStatus->label(),
                    $newStatus->label(),
                    $validated['notes'] ?? '',
                )),
            ]);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Situação atualizada com sucesso.')]);

        return to_route('leases.show', $lease);
    }

    /**
     * Shared reference data used by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formOptions(Request $request): array
    {
        return [
            'properties' => $request->user()->company->properties()
                ->orderBy('title')
                ->get(['id', 'title', 'city', 'state']),
            'owners' => $request->user()->company->owners()
                ->orderBy('name')
                ->get(['id', 'name']),
            'lessees' => $request->user()->company->lessees()
                ->orderBy('name')
                ->get(['id', 'name']),
            'adjustmentIndexes' => $this->adjustmentIndexOptions(),
            'renewalTypes' => $this->renewalTypeOptions(),
        ];
    }

    /**
     * Format a monetary amount as Brazilian currency (R$ 1.234,56).
     */
    private function formatCurrency(float $amount): string
    {
        return 'R$ '.number_format($amount, 2, ',', '.');
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(
            fn (LeaseStatus $status): array => ['value' => $status->value, 'label' => $status->label()],
            LeaseStatus::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function adjustmentIndexOptions(): array
    {
        return array_map(
            fn (LeaseAdjustmentIndex $index): array => ['value' => $index->value, 'label' => $index->label()],
            LeaseAdjustmentIndex::cases(),
        );
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function renewalTypeOptions(): array
    {
        return array_map(
            fn (LeaseRenewalType $type): array => ['value' => $type->value, 'label' => $type->label()],
            LeaseRenewalType::cases(),
        );
    }
}

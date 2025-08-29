<?php

namespace App\Services;

use App\Models\Agreement;
use App\Models\Order;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;

class AgreementService
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly SettingsService $settingsService,
    ) {}

    public function get(
        ?string $query = null,
        ?array $with = null,
        ?array $withCount = null,
        ?int $limit = null,
        ?int $perPage = 24,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc'
    ) {
        $agreementQuery = Agreement::query();

        $agreementQuery->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('content', 'like', "%{$query}%");
        });

        $agreementQuery->when($with, function ($queryBuilder) use ($with) {
            $queryBuilder->with($with);
        });

        $agreementQuery->when($withCount, function ($queryBuilder) use ($withCount) {
            $queryBuilder->withCount($withCount);
        });

        $agreementQuery->when($limit, function ($queryBuilder) use ($limit) {
            $queryBuilder->limit($limit);
        });

        $agreementQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $agreementQuery->get()
            : $agreementQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Agreement
    {
        $deal = null;

        $client = null;

        if ($dealId = data_get($data, 'deal')) {

            $deal = Order::query()->find($dealId);

            if (! $deal) {

                $deal = Purchase::query()->find($dealId);
            }
        }

        if ($clientData = data_get($data, 'client')) {

            $client = $this->clientService->upsert($clientData);
        }

        $attributes = [
            'author_user_id' => data_get($data, 'current_user_id'),
            'store_id' => data_get($data, 'store'),
            'client_id' => $client?->id,
            'client_type' => $client?->getMorphClass(),
            'agreementable_id' => $deal?->id,
            'agreementable_type' => $deal?->getMorphClass(),
            'content' => data_get($data, 'content'),
            'since' => data_get($data, 'since'),
            'until' => data_get($data, 'until'),
        ];

        return Agreement::query()->create($attributes);
    }

    public function update(Agreement $agreement, array $data): Agreement
    {
        $deal = null;

        $client = null;

        if ($dealId = data_get($data, 'deal')) {

            $deal = Order::query()->find($dealId);

            if (! $deal) {

                $deal = Purchase::query()->find($dealId);
            }
        }

        if ($clientData = data_get($data, 'client')) {

            $client = $this->clientService->upsert($clientData);
        }

        $attributes = [
            'store_id' => data_get($data, 'store', $agreement->store_id),
            'client_id' => $client?->id ?? $agreement->client_id,
            'client_type' => $client?->getMorphClass() ?? $agreement->client_type,
            'agreementable_id' => $deal?->id ?? $agreement->agreementable_id,
            'agreementable_type' => $deal?->getMorphClass() ?? $agreement->agreementable_type,
            'content' => data_get($data, 'content', $agreement->content),
            'since' => data_get($data, 'since', $agreement->since),
            'until' => data_get($data, 'until', $agreement->until),
        ];

        return tap($agreement)->update($attributes);
    }

    public function delete(Agreement $agreement, bool $forever = false): ?bool
    {
        if ($forever) {

            return $agreement->forceDelete();
        }

        return $agreement->delete();
    }

    public function generateFile(Agreement $agreement)
    {
        $settings = $this->settingsService->get(group: null);

        $agreement->loadMissing(['author', 'client', 'store', 'agreementable.items.item']);

        $data = compact('agreement', 'settings');

        $pdf = Pdf::setPaper('A4')->loadView('printouts.agreement', $data);

        $pdf->render();

        return $pdf->output();
    }
}

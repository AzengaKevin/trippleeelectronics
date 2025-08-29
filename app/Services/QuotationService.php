<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function __construct(
        private readonly IndividualService $individualService,
        private readonly QuotationItemService $quotationItemService,
        private readonly OrganizationService $organizationService,
        private readonly ClientService $clientService,
        private readonly SettingsService $settingsService,
    ) {}

    public function get(
        ?string $query = null,
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
        ?Store $store = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {

        $quotationQuery = Quotation::query();

        $quotationQuery->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('name', 'like', "%{$query}%");
        });

        $quotationQuery->when($store, function ($queryBuilder) use ($store) {
            $queryBuilder->where('store_id', $store->id);
        });

        $quotationQuery->when($with, function ($queryBuilder) use ($with) {
            $queryBuilder->with($with);
        });

        $quotationQuery->when($withCount, function ($queryBuilder) use ($withCount) {
            $queryBuilder->withCount($withCount);
        });

        $quotationQuery->when($limit, function ($queryBuilder) use ($limit) {
            $queryBuilder->limit($limit);
        });

        $quotationQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $quotationQuery->get()
            : $quotationQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {

            $customer = $this->clientService->upsert(data_get($data, 'customer', []));

            $quotationAttributes = [
                'store_id' => data_get($data, 'store'),
                'author_user_id' => data_get($data, 'author_user_id'),
                'customer_id' => $customer?->id,
                'customer_type' => $customer?->getMorphClass(),
                'amount' => data_get($data, 'amount'),
                'shipping_amount' => data_get($data, 'shipping_amount'),
                'tax_amount' => data_get($data, 'tax_amount'),
                'discount_amount' => data_get($data, 'discount_amount'),
                'total_amount' => data_get($data, 'total_amount'),
                'notes' => data_get($data, 'notes'),
            ];

            $quotation = Quotation::query()->create($quotationAttributes);

            collect(data_get($data, 'items', []))->each(function ($item) use ($quotation, $data) {
                $this->quotationItemService->create([
                    ...$item,
                    'quotation_id' => $quotation->id,
                    'check_stock_availability' => data_get($data, 'check_stock_availability', false),
                ]);
            });

            return $quotation->fresh();
        });
    }

    public function update(Quotation $quotation, array $data): bool
    {

        return DB::transaction(function () use ($quotation, $data) {

            if (isset($data['items']) && is_array($data['items'])) {
                $quotation->items()->delete();

                collect(data_get($data, 'items', []))->each(function ($item) use ($quotation, $data) {
                    $this->quotationItemService->create([
                        ...$item,
                        'quotation_id' => $quotation->id,
                        'check_stock_availability' => data_get($data, 'check_stock_availability', false),
                    ]);
                });
            }

            $customer = $this->clientService->upsert(data_get($data, 'customer', []));

            $quotationAttributes = [
                'store_id' => data_get($data, 'store'),
                'author_user_id' => data_get($data, 'author_user_id'),
                'customer_id' => $customer?->id,
                'customer_type' => $customer?->getMorphClass(),
                'amount' => data_get($data, 'amount'),
                'shipping_amount' => data_get($data, 'shipping_amount'),
                'tax_amount' => data_get($data, 'tax_amount'),
                'discount_amount' => data_get($data, 'discount_amount'),
                'total_amount' => data_get($data, 'total_amount'),
                'notes' => data_get($data, 'notes'),
            ];

            return $quotation->update(array_filter($quotationAttributes));
        });
    }

    public function delete(Quotation $quotation, bool $forever = false): bool
    {
        return DB::transaction(function () use ($quotation, $forever) {

            if ($forever) {

                $quotation->loadMissing('customer');

                $quotation->items()->forceDelete();

                $quotation->customer?->quotations()->detach($quotation);

                return $quotation->forceDelete();
            }

            $quotation->items()->delete();

            return $quotation->delete();
        });
    }

    public function loadPrintingData(Quotation $quotation)
    {

        $settings = $this->settingsService->get(group: null);

        $quotation->loadMissing(['items.item', 'customer', 'store', 'author']);

        $data = compact('quotation', 'settings');

        $pdf = Pdf::setPaper('a4', 'portrait')->loadView('printouts.quotation', $data);

        $pdf->render();

        return $pdf->output();
    }

    public function importRow(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {

            $reference = data_get($data, 'reference');

            $values = [
                'customer_id' => data_get($data, 'customer_id'),
                'customer_type' => data_get($data, 'customer_type'),
                'store_id' => data_get($data, 'store_id'),
                'author_user_id' => data_get($data, 'author_user_id'),
                'amount' => data_get($data, 'amount'),
                'shipping_amount' => data_get($data, 'shipping_amount'),
                'tax_amount' => data_get($data, 'tax_amount'),
                'discount_amount' => data_get($data, 'discount_amount'),
                'total_amount' => data_get($data, 'total_amount'),
                'notes' => data_get($data, 'notes'),
            ];

            $quotation = Quotation::query()->where('reference', $reference)->first();

            if ($quotation) {

                $quotation->fill($values);

                if ($quotation->isDirty()) {
                    $quotation->save();
                }

            } else {

                $quotation = Quotation::query()->create($values);
            }

            return $quotation->fresh();
        });
    }
}

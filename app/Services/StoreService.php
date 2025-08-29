<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
        ?int $perPage = 24,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
        array $user = [],
    ) {
        $storeQuery = Store::search($query, function ($defaultQuery) use ($limit, $user, $with, $withCount) {
            $defaultQuery->when($limit, function ($innerQuery) use ($limit) {
                $innerQuery->limit($limit);
            });

            $defaultQuery->when($with, function ($innerQuery) use ($with) {
                $innerQuery->with($with);
            });

            $defaultQuery->when($withCount, function ($innerQuery) use ($withCount) {
                $innerQuery->withCount($withCount);
            });

            $defaultQuery->when(data_get($user, 'staff'), function ($innerQuery) use ($user) {

                $innerQuery->whereHas('users', function ($nestedInnerQuery) use ($user) {

                    $nestedInnerQuery->where('users.id', data_get($user, 'id'));
                });
            });
        });

        $storeQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $storeQuery->get()
            : $storeQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Store
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'name' => data_get($data, 'name'),
                'short_name' => data_get($data, 'short_name'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'paybill' => data_get($data, 'paybill'),
                'account_number' => data_get($data, 'account_number'),
                'till' => data_get($data, 'till'),
                'kra_pin' => data_get($data, 'kra_pin'),
                'address' => data_get($data, 'address'),
                'location' => data_get($data, 'location'),
            ];

            $store = Store::query()->create($attributes);

            if ($paymentMethods = data_get($data, 'payment_methods')) {

                collect($paymentMethods)->each(function ($paymentMethod) use ($store) {
                    $store->paymentMethods()->attach(
                        $paymentMethod['id'],

                        [
                            'phone_number' => data_get($paymentMethod, 'phone_number'),
                            'paybill_number' => data_get($paymentMethod, 'paybill_number'),
                            'account_number' => data_get($paymentMethod, 'account_number'),
                            'till_number' => data_get($paymentMethod, 'till_number'),
                            'account_name' => data_get($paymentMethod, 'account_name'),
                        ]
                    );
                });
            }

            return $store->fresh();
        });
    }

    public function update(Store $store, array $data): bool
    {

        return DB::transaction(function () use ($store, $data) {

            $attributes = [
                'name' => data_get($data, 'name', $store->name),
                'short_name' => data_get($data, 'short_name', $store->short_name),
                'address' => data_get($data, 'address', $store->address),
                'location' => data_get($data, 'location', $store->location),
                'email' => data_get($data, 'email', $store->email),
                'phone' => data_get($data, 'phone', $store->phone),
                'paybill' => data_get($data, 'paybill', $store->paybill),
                'account_number' => data_get($data, 'account_number', $store->account_number),
                'till' => data_get($data, 'till', $store->till),
                'kra_pin' => data_get($data, 'kra_pin', $store->kra_pin),
            ];

            $store->update($attributes);

            if ($paymentMethods = data_get($data, 'payment_methods')) {

                $store->paymentMethods()->sync(
                    collect($paymentMethods)->mapWithKeys(function ($paymentMethod) {
                        return [
                            $paymentMethod['id'] => [
                                'phone_number' => data_get($paymentMethod, 'phone_number'),
                                'paybill_number' => data_get($paymentMethod, 'paybill_number'),
                                'account_number' => data_get($paymentMethod, 'account_number'),
                                'till_number' => data_get($paymentMethod, 'till_number'),
                                'account_name' => data_get($paymentMethod, 'account_name'),
                            ],
                        ];
                    })->all()
                );
            }

            return true;
        });
    }

    public function delete(Store $store, bool $forever = false): bool
    {
        if ($forever) {
            return $store->forceDelete();
        }

        return $store->delete();
    }

    public function importRow(array $data)
    {
        $attributes = [
            'short_name' => data_get($data, 'short_name'),
        ];

        $values = [
            'name' => data_get($data, 'name'),
            'phone' => data_get($data, 'phone'),
            'paybill' => data_get($data, 'paybill'),
            'account_number' => data_get($data, 'account_number'),
            'till' => data_get($data, 'till'),
            'kra_pin' => data_get($data, 'kra_pin'),
            'address' => data_get($data, 'address'),
            'location' => data_get($data, 'location'),
        ];

        Store::query()->updateOrCreate($attributes, $values);
    }

    public function getUserStore(User $user)
    {

        $user?->loadMissing('roles');

        if ((! $user->hasRole('admin')) && $user->hasRole('staff')) {

            return Store::query()->whereHas('users', function ($innerQuery) use ($user) {
                $innerQuery->where('users.id', $user->id);
            })->firstOrFail();
        }

        return null;
    }
}

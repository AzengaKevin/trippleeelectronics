<?php

namespace App\Services;

use App\Models\Individual;
use Illuminate\Support\Facades\DB;

class IndividualService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?int $perPage = 24,
    ) {
        $individualQuery = Individual::search($query, function ($defaultQuery) use ($limit) {
            $defaultQuery->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            });
        });

        return is_null($perPage)
            ? $individualQuery->get()
            : $individualQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Individual
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'organization_id' => data_get($data, 'organization'),
                'name' => data_get($data, 'name'),
                'username' => data_get($data, 'username'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'address' => data_get($data, 'address'),
                'kra_pin' => data_get($data, 'kra_pin'),
                'id_number' => data_get($data, 'id_number'),
            ];

            $individual = Individual::create($attributes);

            if ($image = data_get($data, 'image')) {

                $individual->addMedia($image)
                    ->preservingOriginal()
                    ->toMediaCollection();
            }

            return $individual->fresh();
        });
    }

    public function update(Individual $individual, array $data): bool
    {
        return DB::transaction(function () use ($individual, $data) {

            $attributes = [
                'organization_id' => data_get($data, 'organization'),
                'name' => data_get($data, 'name'),
                'username' => data_get($data, 'username'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'address' => data_get($data, 'address'),
                'kra_pin' => data_get($data, 'kra_pin'),
                'id_number' => data_get($data, 'id_number'),
            ];

            $individual->update($attributes);

            if ($image = data_get($data, 'image')) {
                $individual->addMedia($image)
                    ->preservingOriginal()
                    ->toMediaCollection();
            }

            return true;
        });
    }

    public function upsert(array $data): ?Individual
    {

        $individualByPhone = Individual::query()
            ->where('phone', data_get($data, 'phone'))
            ->first();

        $individualByEmail = Individual::query()
            ->where('email', data_get($data, 'email'))
            ->first();

        $individual = $individualByPhone ?? $individualByEmail;

        if ($individual) {

            $this->update($individual, [
                'name' => data_get($data, 'name'),
                'username' => data_get($data, 'username'),
                'address' => data_get($data, 'address'),
                'kra_pin' => data_get($data, 'kra_pin'),
                'id_number' => data_get($data, 'id_number'),
            ]);

            return $individual->fresh();
        }

        return $this->create($data);
    }

    public function delete(Individual $individual, bool $forever = false): bool
    {
        if ($forever) {

            $individual->clearMediaCollection();

            return $individual->forceDelete();
        }

        return $individual->delete();
    }

    public function importRow(array $data)
    {
        $attributes = [
            'phone' => data_get($data, 'phone'),
        ];

        $values = [
            'username' => data_get($data, 'username'),
            'organization_id' => data_get($data, 'organization_id'),
            'name' => data_get($data, 'name'),
            'email' => data_get($data, 'email'),
            'address' => data_get($data, 'address'),
            'kra_pin' => data_get($data, 'kra_pin'),
            'id_number' => data_get($data, 'id_number'),
        ];

        Individual::query()->updateOrCreate(
            $attributes,
            $values
        );
    }
}

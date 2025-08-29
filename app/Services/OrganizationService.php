<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationCategory;
use Illuminate\Support\Facades\DB;

class OrganizationService
{
    public function get(
        ?string $query = null,
        ?OrganizationCategory $category = null,
        ?array $with = null,
        ?int $limit = null,
        ?int $perPage = 24,
    ) {

        $organizationQuery = Organization::search($query, function ($defaultQuery) use ($limit, $with, $category) {

            $defaultQuery->when($with, function ($query) use ($with) {
                $query->with($with);
            });

            $defaultQuery->when($category, function ($query) use ($category) {
                $query->where('organization_category_id', $category->id);
            });

            $defaultQuery->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            });
        });

        return is_null($perPage)
            ? $organizationQuery->get()
            : $organizationQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Organization
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'organization_category_id' => data_get($data, 'category'),
                'name' => data_get($data, 'name'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'address' => data_get($data, 'address'),
                'kra_pin' => data_get($data, 'kra_pin'),
            ];

            $organization = Organization::create($attributes);

            if ($image = data_get($data, 'image')) {

                $organization->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return $organization->fresh();
        });
    }

    public function update(Organization $organization, array $data): bool
    {
        return DB::transaction(function () use ($organization, $data) {

            $attributes = [
                'organization_category_id' => data_get($data, 'category', $organization->organization_category_id),
                'name' => data_get($data, 'name', $organization->name),
                'email' => data_get($data, 'email', $organization->email),
                'phone' => data_get($data, 'phone', $organization->phone),
                'address' => data_get($data, 'address', $organization->address),
                'kra_pin' => data_get($data, 'kra_pin', $organization->kra_pin),
            ];

            $organization->update($attributes);

            if ($image = data_get($data, 'image')) {

                $organization->clearMediaCollection();

                $organization->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return $organization->wasChanged();
        });
    }

    public function upsert(array $data): Organization
    {
        $organizationByName = Organization::query()
            ->where('name', data_get($data, 'name'))
            ->first();

        $organizationByEmail = Organization::query()
            ->where('email', data_get($data, 'email'))
            ->first();

        $organization = $organizationByEmail ?? $organizationByName;

        if ($organization) {

            $this->update($organization, [
                'phone' => data_get($data, 'phone'),
                'address' => data_get($data, 'address'),
                'kra_pin' => data_get($data, 'kra_pin'),
            ]);

            return $organization->fresh();
        }

        return $this->create($data);
    }

    public function delete(Organization $organization, bool $forever = false): bool
    {
        if ($forever) {

            return DB::transaction(function () use ($organization) {
                $organization->clearMediaCollection();

                return $organization->forceDelete();
            });
        } else {
            return DB::transaction(function () use ($organization) {
                return $organization->delete();
            });
        }
    }

    public function importRow(array $data): Organization
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'name' => data_get($data, 'name'),
            ];

            $values = [
                'organization_category_id' => data_get($data, 'category'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'address' => data_get($data, 'address'),
                'kra_pin' => data_get($data, 'kra_pin'),
            ];

            return Organization::query()->updateOrCreate($attributes, $values);
        });
    }
}

<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?int $perPage = 24,
    ) {
        $serviceQuery = Service::search($query, function ($defaultQuery) use ($limit) {
            $defaultQuery->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            });
        });

        return is_null($perPage)
            ? $serviceQuery->get()
            : $serviceQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data): Service
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'title' => data_get($data, 'title'),
                'description' => data_get($data, 'description'),
            ];

            $service = Service::query()->create($attributes);

            if ($image = data_get($data, 'image')) {
                $service->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return $service->fresh();
        });
    }

    public function update(Service $service, array $data): bool
    {
        return DB::transaction(function () use ($service, $data) {

            $attributes = [
                'title' => data_get($data, 'title'),
                'description' => data_get($data, 'description'),
            ];

            $service->update($attributes);

            if ($image = data_get($data, 'image')) {
                $service->clearMediaCollection();
                $service->addMedia($image)->preservingOriginal()->toMediaCollection();
            }

            return true;
        });
    }

    public function delete(Service $service, bool $forever = false): bool
    {
        if ($forever) {
            $service->clearMediaCollection();

            return $service->forceDelete();
        }

        return $service->delete();
    }

    public function importRow(array $data)
    {
        $attributes = [
            'title' => data_get($data, 'title'),
        ];

        $values = [
            'author_user_id' => data_get($data, 'author_user_id'),
            'description' => data_get($data, 'description'),
        ];

        Service::query()->updateOrCreate($attributes, $values);
    }
}

<?php

namespace App\Services;

use App\Models\Contact;

class ContactService
{
    public function get(
        ?string $query = null,
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?array $withCount = null,
        ?string $orderBy = 'created_at',
        ?string $orderDirection = 'desc',
    ) {
        $contactQuery = Contact::search($query);

        $contactQuery->when($limit, function ($query) use ($limit) {
            $query->limit($limit);
        });

        $contactQuery->when($with, function ($query) use ($with) {
            $query->with($with);
        });

        $contactQuery->when($withCount, function ($query) use ($withCount) {
            $query->withCount($withCount);
        });

        $contactQuery->orderBy($orderBy, $orderDirection);

        return is_null($perPage)
            ? $contactQuery->get()
            : $contactQuery->paginate(perPage: $perPage);
    }

    public function create(array $data): Contact
    {
        $attributes = [
            'name' => data_get($data, 'name'),
            'email' => data_get($data, 'email'),
            'phone' => data_get($data, 'phone'),
            'message' => data_get($data, 'message'),
        ];

        return Contact::query()->create($attributes);
    }

    public function delete(Contact $contact, bool $forever = false): ?bool
    {
        if ($forever) {
            return $contact->forceDelete();
        }

        return $contact->delete();
    }
}

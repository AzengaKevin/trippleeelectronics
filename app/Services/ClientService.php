<?php

namespace App\Services;

use App\Models\Individual;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use PDO;
use Ramsey\Uuid\Uuid;

class ClientService
{
    public function get(
        ?string $query = null,
        ?int $limit = null,
        ?int $perPage = 24,
    ) {

        $dbDriver = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME);

        $caseInsensitiveNameQuery = match ($dbDriver) {
            'mysql' => 'LOWER(name) LIKE LOWER(?)',
            'pgsql' => 'name ILIKE ?',
            'sqlite' => 'name LIKE ? COLLATE NOCASE',
            default => 'name LIKE ?',
        };

        $individualsQuery = DB::table('individuals')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->select(
                'id',
                'name',
                'email',
                'phone',
                'address',
                'kra_pin',
                DB::raw("'individual' as type")
            );

        $organizationsQuery = DB::table('organizations')
            ->when($query, function ($queryBuilder, $query) use ($caseInsensitiveNameQuery) {
                $queryBuilder->whereRaw($caseInsensitiveNameQuery, ["%{$query}%"]);
            })
            ->select(
                'id',
                'name',
                'email',
                'phone',
                'address',
                'kra_pin',
                DB::raw("'organization' as type")
            );

        $clientsQuery = $individualsQuery->unionAll($organizationsQuery)->orderBy('name')
            ->when($limit, function ($query) use ($limit) {
                $query->limit($limit);
            });

        return is_null($perPage)
            ? $clientsQuery->get()
            : $clientsQuery->paginate($perPage)->withQueryString();
    }

    public function upsert(array $data)
    {

        $id = data_get($data, 'id');

        $client = null;

        $type = data_get($data, 'type');

        if (! empty($id)) {

            $values = [
                'username' => data_get($data, 'username'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'address' => data_get($data, 'address'),
                'kra_pin' => data_get($data, 'kra_pin'),
                'id_number' => data_get($data, 'id_number'),
            ];

            if (Uuid::isValid($id)) {

                $client = Individual::query()->find($id) ?? Organization::query()->find($id);

                $client->fill($values);

                if ($client->isDirty()) {

                    $client->save();
                }
            } else {

                $attributes = [
                    'name' => $id,
                ];

                if ($type === 'organization') {

                    $client = Organization::query()->updateOrCreate($attributes, $values);
                } else {

                    $client = Individual::query()->updateOrCreate($attributes, $values);
                }
            }
        }

        return $client;
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class ActivityService
{
    public function get(
        ?string $query = null,
        ?int $perPage = 96,
        ?array $with = null,
        ?int $limit = null,
        ?User $causer = null,
        string $orderBy = 'created_at',
        string $orderDir = 'desc',
    ) {

        $activityQuery = Activity::query();

        $activityQuery->when($query, function ($innerQuery, $query) {

            $dbDriver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

            $caseInsensitiveColumnQuery = match ($dbDriver) {
                'mysql' => 'LOWER(%s) LIKE LOWER(?)',
                'pgsql' => '%s ILIKE ?',
                'sqlite' => '%s LIKE ? COLLATE NOCASE',
                default => '%s LIKE ?',
            };

            $innerQuery->where(function ($queryBuilder) use ($query, $caseInsensitiveColumnQuery) {
                $queryBuilder->whereRaw(sprintf($caseInsensitiveColumnQuery, 'description'), "%{$query}%")
                    ->orWhereRaw(sprintf($caseInsensitiveColumnQuery, 'log_name'), "%{$query}%");
            });
        });

        $activityQuery->when($with, function ($innerQuery, $with) {

            $innerQuery->with($with);
        });

        $activityQuery->when($limit, function ($innerQuery, $limit) {

            $innerQuery->limit($limit);
        });

        $activityQuery->when($causer, function ($innerQuery, $causer) {

            $innerQuery->where('causer_id', $causer->id);
        });

        $activityQuery->orderBy($orderBy, $orderDir);

        return is_null($perPage)
            ? $activityQuery->get()
            : $activityQuery->paginate($perPage)->withQueryString();
    }

    public function deleteActivity(Activity $activity)
    {
        return $activity->delete();
    }
}

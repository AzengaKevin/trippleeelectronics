<?php

namespace App\Services;

use App\Mail\AccountCreatedMail;
use App\Mail\AccountPasswordUpdatedMail;
use App\Models\Store;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UserService
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly MessageService $messageService
    ) {}

    public function get(
        ?string $query = null,
        ?int $perPage = 24,
        ?int $limit = null,
        ?array $with = null,
        ?Role $role = null,
        ?Store $store = null,
        ?array $roles = null,
    ) {
        $userQuery = User::search($query, function ($queryBuilder) use ($limit, $role, $store, $with, $roles) {
            $queryBuilder->when($limit, function ($queryBuilder, $limit) {
                $queryBuilder->limit($limit);
            });

            $queryBuilder->when($with, function ($queryBuilder, $with) {
                $queryBuilder->with($with);
            });

            $queryBuilder->when($role, function ($queryBuilder, $role) {
                $queryBuilder->whereHas('roles', function ($innerQuery) use ($role) {
                    $innerQuery->where('id', $role->id);
                });
            });

            $queryBuilder->when($roles, function ($queryBuilder, $roles) {

                $queryBuilder->role($roles);
            });

            $queryBuilder->when($store, function ($queryBuilder, $store) {
                $queryBuilder->whereHas('stores', function ($innerQuery) use ($store) {
                    $innerQuery->where('stores.id', $store->id);
                });
            });
        });

        return is_null($perPage)
            ? $userQuery->get()
            : $userQuery->paginate($perPage)->withQueryString();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $attributes = [
                'author_user_id' => data_get($data, 'author_user_id'),
                'name' => data_get($data, 'name'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'password' => $password = data_get($data, 'password', 'password'),
            ];

            $user = User::create($attributes);

            if ($roleIds = data_get($data, 'roles')) {

                $user->roles()->attach($roleIds);
            }

            if ($storeIds = data_get($data, 'stores')) {

                $user->stores()->attach($storeIds);
            }

            Mail::to($user)->send(new AccountCreatedMail($user, $password));

            $user->sendEmailVerificationNotification();

            return $user->fresh();
        });
    }

    public function update(
        User $user,
        array $data,
    ): bool {

        return DB::transaction(function () use ($user, $data) {

            $attributes = [
                'name' => data_get($data, 'name'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
            ];

            if ($roleIds = data_get($data, 'roles')) {

                $user->roles()->sync($roleIds);
            }

            if ($storeIds = data_get($data, 'stores')) {

                $user->stores()->sync($storeIds);
            }

            return $user->update($attributes);
        });
    }

    public function delete(
        User $user,
        bool $destroy = false,
    ) {

        if ($destroy) {

            $user->forceDelete();
        } else {

            $user->delete();
        }
    }

    public function importRow(array $data)
    {
        $attributes = [
            'email' => data_get($data, 'email'),
        ];

        $values = [
            'id' => data_get($data, 'reference', str()->uuid()),
            'name' => data_get($data, 'name'),
            'phone' => data_get($data, 'phone'),
            'email_verified_at' => data_get($data, 'email_verified_at'),
            'phone_verified_at' => data_get($data, 'phone_verified_at'),
            'password' => data_get($data, 'password', Hash::make('password')),
            'created_at' => data_get($data, 'created_at'),
            'updated_at' => data_get($data, 'updated_at'),
            'deleted_at' => data_get($data, 'deleted_at'),
        ];

        DB::table('users')->updateOrInsert(
            $attributes,
            $values
        );
    }

    public function getCurrentUser(?User $user): ?array
    {
        if (is_null($user)) {
            return null;
        }

        $user->loadMissing(['roles:id,name']);

        $avatar = $user->getFirstMedia('avatar');

        $avatarUrl = $avatar ? $avatar->getUrl('preview') : "https://ui-avatars.com/api/?name={$user->name}&size=128&rounded=true";

        return [
            ...$user->only([
                'id',
                'name',
                'email',
                'phone',
                'dob',
                'address',
            ]),
            'avatar_url' => $avatarUrl,
            'last_activity_at' => $user->actions()->latest('created_at')->value('created_at'),
            'roles' => $user->roles->map(fn (Role $role) => $role->only('id', 'name'))->all(),
            'unread_notifications_count' => $user->unreadNotifications->count(),
            'unread_messages_count' => $this->messageService->fetchUnreadMessagesCount($user),
            'notifications' => $user->unreadNotifications()->limit(5)->get()->map(fn (DatabaseNotification $notification) => $notification->only('id', 'type', 'data', 'read_at', 'created_at'))->all(),
        ];
    }

    public function updatePassword(User $user)
    {
        $password = str()->password(length: 8, symbols: false);

        return DB::transaction(function () use ($user, $password) {

            $result = $user->update(compact('password'));

            Mail::to($user)->send(new AccountPasswordUpdatedMail($user, $password));

            return $result;
        });
    }

    public function getOfficials()
    {
        return $this->get(
            perPage: null,
            roles: [
                $this->roleService->createOrUpdateAdminRole(false)->name,
                $this->roleService->createOrUpdateStaffRole(false)->name,
            ]
        );
    }
}

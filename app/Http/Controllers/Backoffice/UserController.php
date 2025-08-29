<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\UserExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\UserImport;
use App\Models\Store;
use App\Models\User;
use App\Services\ExcelService;
use App\Services\RoleService;
use App\Services\StoreService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private readonly UserService $userService,
        private readonly RoleService $roleService,
        private readonly StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $params = $request->only('query', 'role', 'store');

        $filters = [...$params];

        if ($roleId = data_get($filters, 'role')) {
            $filters['role'] = Role::query()->findOrFail($roleId);
        }

        if ($storeId = data_get($filters, 'store')) {
            $filters['store'] = Store::query()->findOrFail($storeId);
        }

        $users = $this->userService->get(...$filters, with: ['roles', 'stores']);

        return Inertia::render('backoffice/users/IndexPage', [
            ...$this->getData(),
            'users' => $users,
            'params' => $params,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('backoffice/users/CreatePage', $this->getData());
    }

    public function store(StoreUserRequest $storeUserRequest): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $storeUserRequest->validated();

        $data['author_user_id'] = request()->user()->id;

        try {

            $this->userService->create($data);

            return $this->sendSuccessRedirect(
                'User created successfully',
                route('backoffice.users.index'),
            );
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Failed to create user',
                $throwable,
            );
        }
    }

    public function show(User $user): Response
    {
        $user->load(['roles', 'stores', 'author']);

        return Inertia::render('backoffice/users/ShowPage', compact('user'));
    }

    public function edit(User $user): Response
    {
        $user->load(['roles', 'stores']);

        return Inertia::render('backoffice/users/EditPage', [
            'user' => $user,
            ...$this->getData(),
        ]);
    }

    public function update(UpdateUserRequest $updateUserRequest, User $user): RedirectResponse
    {

        $data = $updateUserRequest->validated();

        try {

            $this->userService->update($user, $data);

            return $this->sendSuccessRedirect(
                'User updated successfully',
                route('backoffice.users.show', $user),
            );
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Failed to update user',
                $throwable,
            );
        }
    }

    public function destroy(Request $request, string $user): RedirectResponse
    {

        try {

            $destroy = $request->boolean('destroy');

            $user = User::findOrFail($user);

            $this->userService->delete($user, $destroy);

            return $this->sendSuccessRedirect(
                'User deleted successfully',
                route('backoffice.users.index'),
            );
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Failed to delete user',
                $throwable,
            );
        }
    }

    public function export(Request $request)
    {
        $this->authorize('export', User::class);

        $data = $request->only('query', 'limit');

        $userExport = new UserExport($data);

        return $userExport->download(User::getExportFileName());
    }

    public function import()
    {
        $this->authorize('import', User::class);

        return Inertia::render('backoffice/users/ImportPage');
    }

    public function processImport(ImportRequest $importRequest)
    {
        $this->authorize('import', User::class);

        $data = $importRequest->validated();

        try {

            /** @var UploadedFile $file */
            $file = $data['file'];

            $this->robustImport(new UserImport, $file, 'users', 'users');

            return $this->sendSuccessRedirect(
                'Users imported successfully',
                route('backoffice.users.index'),
            );
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Failed to import users',
                $throwable,
            );
        }
    }

    public function updatePassword(User $user): RedirectResponse
    {

        try {

            $this->userService->updatePassword($user);

            return $this->sendSuccessRedirect(
                "You've successfully reset the password for {$user->name}",
                url()->previous(),
            );
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Failed to reset update password',
                $throwable,
            );
        }
    }

    private function getData(): array
    {
        $roles = $this->roleService->get(perPage: null)
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ]);

        $stores = $this->storeService->get(perPage: null)
            ->map(fn ($store) => [
                'id' => $store->id,
                'name' => $store->name,
            ]);

        return [
            'roles' => $roles,
            'stores' => $stores,
        ];
    }
}

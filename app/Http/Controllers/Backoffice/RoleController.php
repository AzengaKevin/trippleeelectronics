<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\RoleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\RoleImport;
use App\Services\ExcelService;
use App\Services\PermissionService;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private RoleService $roleService,
        private PermissionService $permissionService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $params = $request->only('query', 'perPage');

        $roles = $this->roleService->get(...$params, withCount: ['permissions']);

        return Inertia::render('backoffice/roles/IndexPage', compact('roles', 'params'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        $permissions = $this->permissionService->get(perPage: null);

        return Inertia::render('backoffice/roles/CreatePage', compact('permissions'));
    }

    public function store(StoreRoleRequest $storeRoleRequest): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $data = $storeRoleRequest->validated();

        try {
            $this->roleService->create($data);

            return $this->sendSuccessRedirect('Role created successfully.', route('backoffice.roles.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Role creation failed.', $throwable);
        }
    }

    public function show(Role $role)
    {
        $role->load('permissions');

        return Inertia::render('backoffice/roles/ShowPage', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissions = $this->permissionService->get(perPage: null);

        $role->load(['permissions']);

        return Inertia::render('backoffice/roles/EditPage', compact('role', 'permissions'));
    }

    public function update(UpdateRoleRequest $updateRoleRequest, Role $role): RedirectResponse
    {
        $data = $updateRoleRequest->validated();

        try {
            $this->roleService->update($role, $data);

            return $this->sendSuccessRedirect('Role updated successfully.', route('backoffice.roles.show', $role));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Role update failed.', $throwable);
        }
    }

    public function destroy(Request $request, string $role): RedirectResponse
    {
        try {
            $role = Role::findOrFail($role);

            $this->roleService->delete($role, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Role deleted successfully.', route('backoffice.roles.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Role deletion failed.', $throwable);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('export', Role::class);

        $data = $request->only('query', 'limit');

        $roleExport = new RoleExport($data);

        $filename = str('roles')->append('-')->append(now()->toDateString())->append('.xlsx')->value();

        return $roleExport->download($filename);
    }

    public function import()
    {
        $this->authorize('import', Role::class);

        return Inertia::render('backoffice/roles/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $this->authorize('import', Role::class);

        $data = $importRequest->validated();

        try {
            $this->robustImport(new RoleImport, $data['file'], 'roles', 'roles');

            return $this->sendSuccessRedirect('Imported roles', route('backoffice.roles.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to import roles', $throwable);
        }
    }
}

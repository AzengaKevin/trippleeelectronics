<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\PermissionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\PermissionImport;
use App\Services\ExcelService;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(private PermissionService $permissionService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Permission::class);

        $params = $request->only('query', 'perPage');

        $permissions = $this->permissionService->get(...$params);

        return Inertia::render('backoffice/permissions/IndexPage', compact('permissions', 'params'));
    }

    public function create()
    {
        $this->authorize('create', Permission::class);

        return Inertia::render('backoffice/permissions/CreatePage');
    }

    public function store(StorePermissionRequest $storePermissionRequest): RedirectResponse
    {
        $this->authorize('create', Permission::class);

        $data = $storePermissionRequest->validated();

        try {
            $this->permissionService->create($data);

            return $this->sendSuccessRedirect('Permission created successfully.', route('backoffice.permissions.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Permission creation failed.', $throwable);
        }
    }

    public function show(Permission $permission)
    {
        return Inertia::render('backoffice/permissions/ShowPage', compact('permission'));
    }

    public function edit(Permission $permission)
    {
        return Inertia::render('backoffice/permissions/EditPage', compact('permission'));
    }

    public function update(UpdatePermissionRequest $updatePermissionRequest, Permission $permission): RedirectResponse
    {
        $data = $updatePermissionRequest->validated();

        try {
            $this->permissionService->update($permission, $data);

            return $this->sendSuccessRedirect('Permission updated successfully.', route('backoffice.permissions.show', $permission));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Permission update failed.', $throwable);
        }
    }

    public function destroy(Request $request, string $permission): RedirectResponse
    {
        try {
            $permission = Permission::findOrFail($permission);

            $this->permissionService->delete($permission, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Permission deleted successfully.', route('backoffice.permissions.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Permission deletion failed.', $throwable);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('export', Permission::class);

        $data = $request->only('query', 'limit');

        $permissionExport = new PermissionExport($data);

        $filename = str('permissions')->append('-')->append(now()->toDateString())->append('.xlsx')->value();

        return $permissionExport->download($filename);
    }

    public function import()
    {
        $this->authorize('import', Permission::class);

        return Inertia::render('backoffice/permissions/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $this->authorize('import', Permission::class);

        $data = $importRequest->validated();

        try {
            $this->robustImport(new PermissionImport, $data['file'], 'permissions', 'permissions');

            return $this->sendSuccessRedirect('Imported permissions', route('backoffice.permissions.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to import permissions', $throwable);
        }
    }
}

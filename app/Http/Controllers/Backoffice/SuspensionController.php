<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSuspensionRequest;
use App\Http\Requests\UpdateSuspensionRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Employee;
use App\Models\Suspension;
use App\Services\EmployeeService;
use App\Services\SuspensionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuspensionController extends Controller
{
    use RedirectWithFeedback;

    private ?Employee $currentEmployee = null;

    public function __construct(
        private readonly SuspensionService $suspensionService,
        private readonly EmployeeService $employeeService,
    ) {
        $this->currentEmployee = Employee::query()->where('user_id', request()->user()->id)->first();
    }

    public function index(Request $request): Response
    {
        $params = $request->only('employee');

        $filters = [];

        if (! is_null($this->currentEmployee)) {

            $filters['employee'] = $this->currentEmployee;
        } else {

            if ($employeeId = data_get($params, 'employee')) {

                $filters['employee'] = Employee::query()->findOrFail($employeeId);
            }
        }

        $suspensions = $this->suspensionService->get(...$filters, with: ['employee']);

        return Inertia::render('backoffice/suspensions/IndexPage', [
            'suspensions' => $suspensions,
            'employees' => $this->employeeService->get(perPage: null),
            'params' => $params,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('backoffice/suspensions/CreatePage', [
            'employees' => $this->employeeService->get(perPage: null),
        ]);
    }

    public function store(StoreSuspensionRequest $storeSuspensionRequest)
    {
        $data = $storeSuspensionRequest->validated();

        try {

            $this->suspensionService->create($data);

            return $this->sendSuccessRedirect("You've successfully created an employee suspension", route('backoffice.suspensions.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Creating a suspension failed', $throwable);
        }
    }

    public function show(Suspension $suspension): Response
    {
        $suspension->load('employee');

        return Inertia::render('backoffice/suspensions/ShowPage', compact('suspension'));
    }

    public function edit(Suspension $suspension): Response
    {
        $suspension->load('employee');

        return Inertia::render('backoffice/suspensions/EditPage', [
            'suspension' => $suspension,
            'employees' => $this->employeeService->get(perPage: null),
        ]);
    }

    public function update(UpdateSuspensionRequest $updateSuspensionRequest, Suspension $suspension)
    {
        $data = $updateSuspensionRequest->validated();

        try {

            $this->suspensionService->update($suspension, $data);

            return $this->sendSuccessRedirect("You've successfully update the suspension", route('backoffice.suspensions.show', $suspension));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Updating a suspension failed', $throwable);
        }
    }

    public function destroy(Suspension $suspension): RedirectResponse
    {

        try {

            $this->suspensionService->delete($suspension);

            return $this->sendSuccessRedirect('Suspension deleted successfully.', route('backoffice.suspensions.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'An error occurred while deleting the suspension',
                $throwable,
            );
        }
    }
}

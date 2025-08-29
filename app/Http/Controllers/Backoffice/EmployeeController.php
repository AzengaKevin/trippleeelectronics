<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(private readonly EmployeeService $employeeService) {}

    public function index(Request $request)
    {

        $params = $request->only('query');

        $employees = $this->employeeService->get(...$params);

        return Inertia::render('backoffice/employees/IndexPage', [
            'employees' => $employees,
            'params' => $params,
        ]);
    }

    public function show(Employee $employee)
    {
        $employee->load(['media', 'user']);

        $employee = [
            ...$employee->only('id', 'name', 'email', 'phone', 'position', 'department', 'identification_number', 'kra_pin', 'hire_date', 'created_at', 'updated_at'),
            'media' => $employee->media->map(fn ($media) => [
                ...$media->only('id', 'type', 'name', 'file_name', 'mime_type'),
                'url' => $media->getUrl(),
                'size' => round($media->size / 1024, 2),
                'created_at' => $media->created_at->toDateString(),
            ]),
        ];

        return Inertia::render('backoffice/employees/ShowPage', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        return Inertia::render('backoffice/employees/EditPage', [
            'employee' => $employee,
        ]);
    }

    public function update(UpdateEmployeeRequest $updateEmployeeRequest, Employee $employee)
    {
        $payload = $updateEmployeeRequest->validated();

        try {

            $this->employeeService->update($employee, $payload);

            return $this->sendSuccessRedirect('Employee updated successfully', route('backoffice.employees.show', $employee));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to update empoyee', $throwable);
        }
    }

    public function suspend(Employee $employee)
    {

        try {

            $this->employeeService->suspend($employee);

            return $this->sendSuccessRedirect("You've successfully suspended {$employee->name}", url()->previous());

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to suspend the employee', $throwable);
        }
    }

    public function destroy(Employee $employee)
    {
        try {

            $this->employeeService->delete($employee);

            return $this->sendSuccessRedirect('Employee deleted successfully', route('backoffice.employees.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to delete employee', $throwable);
        }
    }
}

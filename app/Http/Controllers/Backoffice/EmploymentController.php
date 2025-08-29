<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmploymentRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Employee;
use App\Models\Enums\EmployeeDocument;
use App\Services\EmployeeService;
use Inertia\Inertia;

class EmploymentController extends Controller
{
    use RedirectWithFeedback;

    private ?Employee $employee = null;

    public function __construct(private readonly EmployeeService $employeeService)
    {

        $this->employee = request()->user()->employee()->first();
    }

    public function show()
    {
        abort_unless($this->employee, 404, 'Employment not found.');

        $this->employee->load(['media', 'user']);

        $employee = [
            ...$this->employee->only('id', 'name', 'email', 'phone', 'position', 'department', 'identification_number', 'kra_pin', 'hire_date', 'created_at', 'updated_at'),
            'media' => $this->employee->media->map(fn ($media) => [
                ...$media->only('id', 'type', 'name', 'file_name', 'mime_type'),
                'url' => $media->getUrl(),
                'size' => round($media->size / 1024, 2),
                'created_at' => $media->created_at->toDateString(),
            ]),
        ];

        return Inertia::render('backoffice/employment/ShowPage', compact('employee'));
    }

    public function edit()
    {

        abort_unless($this->employee, 404, 'Employment not found.');

        return Inertia::render('backoffice/employment/EditPage', [
            'employee' => $this->employee,
            'documentTypes' => EmployeeDocument::labelledOptions(),
        ]);
    }

    public function update(UpdateEmploymentRequest $updateEmploymentRequest)
    {

        abort_unless($this->employee, 404, 'Employment not found.');

        $data = $updateEmploymentRequest->validated();

        try {

            $this->employeeService->update($this->employee, $data);

            return $this->sendSuccessRedirect('Employment details updated successfully.', route('backoffice.employment.show'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to update employment details', $throwable);
        }
    }
}

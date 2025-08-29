<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Enums\ContractStatus;
use App\Models\Enums\ContractType;
use App\Services\ContractService;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContractController extends Controller
{
    use RedirectWithFeedback;

    private ?Employee $currentEmployee = null;

    public function __construct(
        private readonly ContractService $contractService,
        private readonly EmployeeService $employeeService,
    ) {
        $this->currentEmployee = Employee::query()->where('user_id', request()->user()->id)->first();
    }

    public function index(Request $request): Response
    {
        $params = $request->only('employee', 'status', 'type');

        $filters = [];

        if (! is_null($this->currentEmployee)) {

            $filters['employee'] = $this->currentEmployee;
        } else {

            if ($employeeId = data_get($params, 'employee')) {

                $filters['employee'] = Employee::query()->findOrFail($employeeId);
            }
        }

        if ($statusValue = data_get($params, 'status')) {

            $filters['contractStatus'] = ContractStatus::tryFrom($statusValue);
        }

        if ($typeValue = data_get($params, 'type')) {

            $filters['contractType'] = ContractType::tryFrom($typeValue);
        }

        $contracts = $this->contractService->get(...$filters, with: ['employee']);

        return Inertia::render('backoffice/contracts/IndexPage', [
            'contracts' => $contracts,
            'employees' => $this->employeeService->get(perPage: null),
            'types' => ContractType::labelledOptions(),
            'statuses' => ContractStatus::labelledOptions(),
            'params' => $params,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('backoffice/contracts/CreatePage', [
            'employees' => $this->employeeService->get(perPage: null),
            'types' => ContractType::labelledOptions(),
            'statuses' => ContractStatus::labelledOptions(),
        ]);
    }

    public function store(StoreContractRequest $storeContractRequest)
    {
        $data = $storeContractRequest->validated();

        try {

            $this->contractService->create($data);

            return $this->sendSuccessRedirect("You've successfully created an employee contract", route('backoffice.contracts.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Creating a contract failed', $throwable);
        }
    }

    public function show(Contract $contract): Response
    {
        $contract->load('employee');

        return Inertia::render('backoffice/contracts/ShowPage', compact('contract'));
    }

    public function edit(Contract $contract): Response
    {
        $contract->load('employee');

        return Inertia::render('backoffice/contracts/EditPage', [
            'contract' => $contract,
            'employees' => $this->employeeService->get(perPage: null),
            'types' => ContractType::labelledOptions(),
            'statuses' => ContractStatus::labelledOptions(),
        ]);
    }

    public function update(UpdateContractRequest $updateContractRequest, Contract $contract)
    {
        $data = $updateContractRequest->validated();

        try {

            $this->contractService->update($contract, $data);

            return $this->sendSuccessRedirect("You've successfully update the contract", route('backoffice.contracts.show', $contract));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Updating a contract failed', $throwable);
        }
    }

    public function destroy(Contract $contract): RedirectResponse
    {

        try {

            $this->contractService->delete($contract);

            return $this->sendSuccessRedirect('Contract deleted successfully.', route('backoffice.contracts.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'An error occurred while deleting the contract',
                $throwable,
            );
        }
    }
}

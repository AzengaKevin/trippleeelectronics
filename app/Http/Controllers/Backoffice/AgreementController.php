<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgreementRequest;
use App\Http\Requests\UpdateAgreementRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Agreement;
use App\Services\AgreementService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgreementController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(
        private readonly AgreementService $agreementService,
        private readonly StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $params = $request->only('query');

        $agreements = $this->agreementService->get(...$params, with: ['author', 'client', 'store']);

        return Inertia::render('backoffice/agreements/IndexPage', [
            'agreements' => $agreements,
            'params' => $params,
        ]);
    }

    public function create(): Response
    {
        $stores = $this->storeService->get(perPage: null, orderBy: 'name', orderDirection: 'asc')->map(fn ($store) => $store->only('id', 'name'));

        return Inertia::render('backoffice/agreements/CreatePage', [
            'stores' => $stores,
        ]);
    }

    public function store(StoreAgreementRequest $storeAgreementRequest): RedirectResponse
    {

        $data = $storeAgreementRequest->validated();

        try {

            $agreement = $this->agreementService->create([
                ...$data,
                'current_user_id' => request()->user()->id,
            ]);

            return $this->sendSuccessRedirect('The agreement has been created successfully', route('backoffice.agreements.index', [
                'agreement' => $agreement->id,
            ]));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('An error occurred while creating the agreement', $throwable);
        }
    }

    public function show(Agreement $agreement): Response
    {
        $agreement->load(['author', 'client', 'store']);

        return Inertia::render('backoffice/agreements/ShowPage', [
            'agreement' => $agreement,
        ]);
    }

    public function edit(Agreement $agreement): Response
    {
        $agreement->load(['author', 'client', 'store']);

        $stores = $this->storeService->get(perPage: null, orderBy: 'name', orderDirection: 'asc')->map(fn ($store) => $store->only('id', 'name'));

        return Inertia::render('backoffice/agreements/EditPage', [
            'agreement' => $agreement,
            'stores' => $stores,
        ]);
    }

    public function update(UpdateAgreementRequest $updateAgreementRequest, Agreement $agreement): RedirectResponse
    {
        $data = $updateAgreementRequest->validated();

        try {

            $agreement = $this->agreementService->update($agreement, [
                ...$data,
                'current_user_id' => request()->user()->id,
            ]);

            return $this->sendSuccessRedirect('The agreement has been updated successfully', route('backoffice.agreements.show', [
                'agreement' => $agreement->id,
            ]));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('An error occurred while updating the agreement', $throwable);
        }
    }

    public function destroy(Agreement $agreement): RedirectResponse
    {
        try {

            $this->agreementService->delete($agreement, request()->boolean('forever'));

            return $this->sendSuccessRedirect('The agreement has been deleted successfully', route('backoffice.agreements.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('An error occurred while deleting the agreement', $throwable);
        }
    }

    public function print(Agreement $agreement)
    {
        activity()->performedOn($agreement)
            ->causedBy(request()->user())
            ->log('Printed the agreement');

        $pdfContent = $this->agreementService->generateFile($agreement);

        $filename = str($agreement->id)
            ->append('-')
            ->append('agreement')
            ->slug()
            ->append('.pdf')
            ->value();

        $contentDisposition = 'inline; filename="'.$filename.'"';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $contentDisposition);
    }
}

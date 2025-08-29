<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\IndividualExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreIndividualRequest;
use App\Http\Requests\UpdateIndividualRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\IndividualImport;
use App\Models\Individual;
use App\Services\ExcelService;
use App\Services\IndividualService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndividualController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(private IndividualService $individualService) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'perPage');

        $individuals = $this->individualService->get(...$params);

        return Inertia::render('backoffice/individuals/IndexPage', compact('individuals', 'params'));
    }

    public function create()
    {
        return Inertia::render('backoffice/individuals/CreatePage');
    }

    public function store(StoreIndividualRequest $storeIndividualRequest): RedirectResponse
    {
        $data = $storeIndividualRequest->validated();

        try {

            $currentUser = request()->user();

            $data['author_user_id'] = $currentUser->id;

            $this->individualService->create($data);

            return $this->sendSuccessRedirect('Individual created successfully.', route('backoffice.individuals.index'));

        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect(
                'Individual creation failed.',
                $throwable
            );

        }
    }

    public function show(Individual $individual)
    {
        $individual->load('author');

        $individual->image_url = $individual->getFirstMediaUrl();

        return Inertia::render('backoffice/individuals/ShowPage', compact('individual'));
    }

    public function edit(Individual $individual)
    {
        return Inertia::render('backoffice/individuals/EditPage', compact('individual'));
    }

    public function update(UpdateIndividualRequest $updateIndividualRequest, Individual $individual): RedirectResponse
    {
        $data = $updateIndividualRequest->validated();

        try {

            $this->individualService->update($individual, $data);

            return $this->sendSuccessRedirect('Individual updated successfully.', route('backoffice.individuals.show', $individual));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Individual update failed.',
                $throwable
            );

        }
    }

    public function destroy(Request $request, string $individual): RedirectResponse
    {
        try {

            $individual = Individual::findOrFail($individual);

            $this->individualService->delete($individual, $request->boolean('forever'));

            return $this->sendSuccessRedirect('Individual deleted successfully.', route('backoffice.individuals.index'));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect(
                'Individual deletion failed.',
                $throwable
            );

        }
    }

    public function export(Request $request)
    {

        $data = $request->only('query', 'limit');

        $individualExport = new IndividualExport($data);

        return $individualExport->download(Individual::getExportFilename());
    }

    public function import()
    {
        return Inertia::render('backoffice/individuals/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $this->robustImport(new IndividualImport, $data['file'], 'individuals', 'individuals');

            return $this->sendSuccessRedirect('Imported individuals', route('backoffice.individuals.index'));

        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to import individuals', $throwable);

        }
    }
}

<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Tax;
use App\Services\JurisdictionService;
use App\Services\TaxService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(
        private readonly TaxService $taxService,
        private readonly JurisdictionService $jurisdictionService,
    ) {}

    public function index(Request $request)
    {
        $params = $request->only('query');

        $taxes = $this->taxService->get(...$params);

        return Inertia::render('backoffice/taxes/IndexPage', [
            'params' => $params,
            'taxes' => $taxes,
        ]);
    }

    public function create()
    {
        $jurisdictions = $this->jurisdictionService->get(perPage: null);

        return Inertia::render('backoffice/taxes/CreatePage', [
            'jurisdictions' => $jurisdictions,
        ]);
    }

    public function store(StoreTaxRequest $storeTaxRequest)
    {

        $data = $storeTaxRequest->validated();

        try {

            $this->taxService->create($data);

            return $this->sendSuccessRedirect('Tax successfully created', route('backoffice.taxes.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to create tax', $throwable);
        }
    }

    public function show(Tax $tax)
    {
        return Inertia::render('backoffice/taxes/ShowPage', [
            'tax' => $tax,
        ]);
    }

    public function edit(Tax $tax)
    {
        $jurisdictions = $this->jurisdictionService->get(perPage: null);

        return Inertia::render('backoffice/taxes/EditPage', [
            'tax' => $tax,
            'jurisdictions' => $jurisdictions,
        ]);
    }

    public function update(UpdateTaxRequest $updateTaxRequest, Tax $tax)
    {
        $data = $updateTaxRequest->validated();

        try {

            $this->taxService->update($tax, $data);

            return $this->sendSuccessRedirect('Tax successfully updated', route('backoffice.taxes.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to update tax', $throwable);
        }
    }

    public function destroy(Tax $tax)
    {
        try {

            $this->taxService->delete($tax);

            return $this->sendSuccessRedirect('Tax successfully deleted', route('backoffice.taxes.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to delete tax', $throwable);
        }
    }
}

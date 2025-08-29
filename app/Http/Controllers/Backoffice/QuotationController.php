<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\QuotationExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\QuotationImport;
use App\Models\Quotation;
use App\Models\Store;
use App\Models\User;
use App\Services\ClientService;
use App\Services\ExcelService;
use App\Services\ProductService;
use App\Services\QuotationService;
use App\Services\SettingsService;
use App\Services\StoreService;
use App\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    private ?User $currentUser = null;

    private ?Store $currentStore = null;

    public function __construct(
        private readonly QuotationService $quotationService,
        private readonly ClientService $clientService,
        private readonly ProductService $productService,
        private readonly SettingsService $settingsService,
        private readonly StoreService $storeService,
        private readonly TaxService $taxService,
    ) {

        $this->currentUser = request()->user();

        $this->currentStore = $this->storeService->getUserStore($this->currentUser);
    }

    public function index(Request $request): Response
    {
        $params = $request->only('query', 'store');

        $filters = [...$params];

        if ($this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if (! is_null($storeId = data_get($filters, 'store')) && $this->currentUser->hasRole('admin')) {

            $filters['store'] = Store::query()->find($storeId);
        }

        $quotations = $this->quotationService->get(...$filters, with: ['author', 'customer', 'store']);

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        return Inertia::render('backoffice/quotations/IndexPage', [
            'quotations' => $quotations,
            'filters' => $filters,
            'stores' => $stores,
            'params' => $params,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('backoffice/quotations/CreatePage', $this->getQuotationData());
    }

    public function store(StoreQuotationRequest $storeQuotationRequest): RedirectResponse
    {
        $data = $storeQuotationRequest->validated();

        $data['author_user_id'] = $this->currentUser->id;

        $data['check_stock_availability'] = false;

        try {

            $quotation = $this->quotationService->create($data);

            return $this->sendSuccessRedirect('Quotation created successfully.', route('backoffice.quotations.show', $quotation->id));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to create quotation', $throwable);
        }
    }

    public function show(Quotation $quotation): Response
    {
        $quotation->load(['items.item', 'customer', 'author', 'store']);

        $settings = $this->settingsService->get();

        return Inertia::render('backoffice/quotations/ShowPage', [
            'quotation' => $quotation,
            'settings' => $settings,
        ]);
    }

    public function edit(Quotation $quotation): Response
    {
        $quotation->load(['items.item', 'customer']);

        return Inertia::render('backoffice/quotations/EditPage', [
            'quotation' => $quotation,
            ...$this->getQuotationData(),
        ]);
    }

    public function update(UpdateQuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $data = $request->validated();

        $data['check_stock_availability'] = false;

        try {

            $this->quotationService->update($quotation, $data);

            return $this->sendSuccessRedirect('Quotation updated successfully.', route('backoffice.quotations.show', $quotation->id));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to update quotation', $throwable);
        }
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        try {

            $this->quotationService->delete($quotation);

            return $this->sendSuccessRedirect('Quotation deleted successfully.', route('backoffice.quotations.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to delete quotation', $throwable);
        }
    }

    public function export(Request $request)
    {
        $params = $request->only('query', 'store', 'limit');

        $filters = [...$params];

        if ($this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if (! is_null($storeId = data_get($filters, 'store')) && $this->currentUser->hasRole('admin')) {

            $filters['store'] = Store::query()->find($storeId);
        }

        $quotationExport = new QuotationExport($filters);

        $filename = Quotation::getExportFilename();

        return $quotationExport->download($filename);
    }

    public function import(): Response
    {
        return Inertia::render('backoffice/quotations/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $data = $importRequest->validated();

        try {

            $file = data_get($data, 'file');

            $import = new QuotationImport;

            $this->robustImport($import, $file, 'quotations', 'quotations');

            return $this->sendSuccessRedirect('Imported quotations successfully.', route('backoffice.quotations.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to import quotations.', $throwable);
        }
    }

    public function print(Quotation $quotation)
    {

        $pdfContent = $this->quotationService->loadPrintingData($quotation);

        $filename = str($quotation->reference)
            ->append('-')
            ->append('receipt')
            ->slug()
            ->append('.pdf')
            ->value();

        $contentDisposition = 'inline; filename="'.$filename.'"';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $contentDisposition);
    }

    private function getQuotationData(): array
    {
        /** @var User $currentUser */
        $currentUser = request()->user();

        $stores = $this->storeService->get(perPage: null, user: ['id' => $currentUser->id, 'staff' => $currentUser->hasRole('staff')])->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        $primaryTax = $this->taxService->fetchPrimaryTax();

        return ['stores' => $stores, 'primaryTax' => $primaryTax];
    }
}

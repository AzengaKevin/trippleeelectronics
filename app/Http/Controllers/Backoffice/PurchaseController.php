<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\PurchaseExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Imports\PurchaseImport;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\User;
use App\Services\ClientService;
use App\Services\ExcelService;
use App\Services\ItemService;
use App\Services\PaymentMethodService;
use App\Services\PaymentService;
use App\Services\ProductService;
use App\Services\PurchaseService;
use App\Services\StoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    private ?User $currentUser = null;

    private ?Store $currentStore = null;

    public function __construct(
        private readonly PurchaseService $purchaseService,
        private readonly PaymentMethodService $paymentMethodService,
        private readonly ItemService $itemService,
        private readonly ProductService $productService,
        private readonly ClientService $clientService,
        private readonly StoreService $storeService,
        private readonly PaymentService $paymentService,
    ) {
        $this->currentUser = request()->user();

        $this->currentStore = $this->storeService->getUserStore($this->currentUser);
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Purchase::class);

        $params = $request->only('query', 'store', 'withoutStore');

        $filters = [...$params];

        $filters['withoutStore'] = $request->boolean('withoutStore', false);

        if ($this->currentUser->hasRole('staff')) {
            $filters['store'] = $this->currentStore;
        }

        if (! is_null($storeId = data_get($filters, 'store')) && $this->currentUser->hasRole('admin')) {

            $filters['store'] = Store::query()->find($storeId);
        }

        $purchases = $this->purchaseService->get(...$filters, with: ['store', 'author', 'supplier']);

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        return Inertia::render('backoffice/purchases/IndexPage', compact('purchases', 'params', 'stores'));
    }

    public function create(): Response
    {
        $this->authorize('create', Purchase::class);

        return Inertia::render('backoffice/purchases/CreatePage', $this->getPurchaseFormData());
    }

    public function store(StorePurchaseRequest $storePurchaseRequest): RedirectResponse
    {
        $this->authorize('create', Purchase::class);

        $data = $storePurchaseRequest->validated();

        $data['author_user_id'] = request()->user()->id;

        try {

            $purchase = DB::transaction(function () use ($data) {

                $purchase = $this->purchaseService->create($data);

                if ($paymentsData = data_get($data, 'payments')) {

                    collect($paymentsData)->each(function ($paymentItem) use ($purchase) {

                        $this->paymentService->createPurchasePayment($purchase, [
                            ...$paymentItem,
                            'author_user_id' => request()->user()->id,
                            'payment_method_id' => data_get($paymentItem, 'payment_method', null),
                        ]);
                    });
                }

                return $purchase->fresh();
            });

            return $this->sendSuccessRedirect('Purchase created successfully', route('backoffice.purchases.show', $purchase->id));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to create purchase', $throwable);
        }
    }

    public function show(Purchase $purchase): Response
    {
        $purchase->load(['items.item', 'supplier', 'store', 'payments.paymentMethod']);

        return Inertia::render('backoffice/purchases/ShowPage', [
            'purchase' => $purchase,
            ...$this->getPurchaseFormData(),
        ]);
    }

    public function edit(Purchase $purchase): Response
    {
        $purchase->load(['items.item', 'supplier', 'store', 'payments.paymentMethod']);

        return Inertia::render('backoffice/purchases/EditPage', [
            'purchase' => $purchase,
            ...$this->getPurchaseFormData(),
        ]);
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $data = $request->validated();

        try {

            $this->purchaseService->update($purchase, $data);

            return $this->sendSuccessRedirect('Purchase updated successfully', route('backoffice.purchases.show', $purchase->id));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to update purchase', $throwable);
        }
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        try {
            $this->purchaseService->delete($purchase);

            return $this->sendSuccessRedirect('Purchase deleted successfully', route('backoffice.purchases.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to delete purchase', $throwable);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('export', Purchase::class);

        $data = $request->only('query', 'limit');

        $export = new PurchaseExport($data);

        return $export->download(Purchase::getExportFilename());
    }

    public function import()
    {
        $this->authorize('import', Purchase::class);

        return Inertia::render('backoffice/purchases/ImportPage');
    }

    public function processImport(ImportRequest $importRequest): RedirectResponse
    {
        $this->authorize('import', Purchase::class);

        $data = $importRequest->validated();

        try {
            $this->robustImport(new PurchaseImport, $data['file'], 'purchases', 'purchases');

            return $this->sendSuccessRedirect('Imported purchases successfully.', route('backoffice.purchases.index'));
        } catch (\Throwable $throwable) {
            return $this->sendErrorRedirect('Failed to import purchases.', $throwable);
        }
    }

    private function getPurchaseFormData(): array
    {
        /** @var User $currentUser */
        $currentUser = request()->user();

        $stores = $this->storeService->get(perPage: null, user: ['id' => $currentUser->id, 'staff' => $currentUser->hasRole('staff')])->map(fn ($store) => $store->only(['id', 'name']));

        $paymentMethods = $this->paymentMethodService->get(perPage: null)->map(fn ($m) => $m->only(['id', 'name']));

        return [
            'stores' => $stores,
            'paymentMethods' => $paymentMethods,
        ];
    }
}

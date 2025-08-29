<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Enums\OrderStatus;
use App\Models\Store;
use App\Models\User;
use App\Services\OrderService;
use App\Services\SalesService;
use App\Services\StoreService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    use RedirectWithFeedback;

    private ?User $currentUser = null;

    private ?Store $currentStore = null;

    public function __construct(
        private StoreService $storeService,
        private UserService $userService,
        private OrderService $orderService,
        private SalesService $salesService,
    ) {
        $this->currentUser = request()->user();
        $this->currentStore = $this->storeService->getUserStore($this->currentUser);
    }

    public function index(Request $request): Response
    {
        $params = $request->only('store', 'status', 'author', 'from', 'to', 'mine');

        $filters = [];

        if ($this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if ($this->currentUser->hasRole('admin')) {

            $storeId = data_get($params, 'store');

            $filters['store'] = Store::query()->find($storeId);
        }

        if ($authorId = data_get($params, 'author')) {

            $filters['author'] = User::query()->find($authorId);
        }

        if ($request->boolean('mine')) {

            $filters['author'] = $this->currentUser;
        }

        if ($status = data_get($params, 'status')) {

            $filters['status'] = $status;
        }

        if ($from = data_get($params, 'from')) {

            $filters['from'] = $from;
        } else {

            $filters['from'] = now()->toDateString();
        }

        if ($to = data_get($params, 'to')) {

            $filters['to'] = $to;
        } else {

            $filters['to'] = now()->toDateString();
        }

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        $authors = $this->userService->getOfficials()->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
        ]);

        $statistics = $this->orderService->getStatistics($filters);

        $orderStatuses = OrderStatus::labelledOptions();

        return Inertia::render('backoffice/reports/IndexPage', compact('stores', 'authors', 'orderStatuses', 'params', 'statistics'));
    }

    public function print(Request $request)
    {
        $params = $request->only('store', 'status', 'author', 'from', 'to', 'mine');

        $filters = [];

        if ($this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if ($this->currentUser->hasRole('admin')) {

            $storeId = data_get($params, 'store');

            $filters['store'] = Store::query()->find($storeId);
        }

        if ($authorId = data_get($params, 'author')) {

            $filters['author'] = User::query()->find($authorId);
        }

        if ($request->boolean('mine')) {

            $filters['author'] = $this->currentUser;
        }

        if ($status = data_get($params, 'status')) {

            $filters['status'] = $status;
        }

        if ($from = data_get($params, 'from')) {

            $filters['from'] = $from;
        } else {

            $filters['from'] = now()->toDateString();
        }

        if ($to = data_get($params, 'to')) {

            $filters['to'] = $to;
        } else {

            $filters['to'] = now()->toDateString();
        }

        $pdfContent = $this->orderService->generateOrdersReportsDetails($filters);

        $filename = str('orders-reports-details')
            ->append('-')
            ->append(date('Y-m-d'))
            ->slug()
            ->append('.pdf')
            ->value();

        $contentDisposition = 'inline; filename="'.$filename.'"';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $contentDisposition);
    }

    public function pnl(Request $request): Response
    {
        $params = $request->only('store', 'author', 'from', 'to', 'mine');

        $filters = [];

        if ($this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if ($this->currentUser->hasRole('admin')) {

            $storeId = data_get($params, 'store');

            $filters['store'] = Store::query()->find($storeId);
        }

        if ($authorId = data_get($params, 'author')) {

            $filters['author'] = User::query()->find($authorId);
        }

        if ($request->boolean('mine')) {

            $filters['author'] = $this->currentUser;
        }

        if ($from = data_get($params, 'from')) {

            $filters['from'] = $from;
        } else {

            $filters['from'] = now()->toDateString();
        }

        if ($to = data_get($params, 'to')) {

            $filters['to'] = $to;
        } else {

            $filters['to'] = now()->toDateString();
        }

        $stores = $this->storeService->get(perPage: null)->map(fn ($store) => [
            'id' => $store->id,
            'name' => $store->name,
        ]);

        $authors = $this->userService->getOfficials()->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
        ]);

        $items = $this->salesService->getRawItems(...$filters);

        return Inertia::render('backoffice/reports/PnlPage', compact('stores', 'authors', 'params', 'items'));
    }

    public function pnlPrint(Request $request)
    {
        $params = $request->only('store', 'author', 'from', 'to', 'mine');

        $filters = [];

        if ($this->currentUser->hasRole('staff')) {

            $filters['store'] = $this->currentStore;
        }

        if ($this->currentUser->hasRole('admin')) {

            $storeId = data_get($params, 'store');

            $filters['store'] = Store::query()->find($storeId);
        }

        if ($authorId = data_get($params, 'author')) {

            $filters['author'] = User::query()->find($authorId);
        }

        if ($request->boolean('mine')) {

            $filters['author'] = $this->currentUser;
        }

        if ($from = data_get($params, 'from')) {

            $filters['from'] = $from;
        } else {

            $filters['from'] = now()->toDateString();
        }

        if ($to = data_get($params, 'to')) {

            $filters['to'] = $to;
        } else {

            $filters['to'] = now()->toDateString();
        }

        $pdfContent = $this->salesService->generatePnlReportDetails(...$filters);

        $filename = str('pnl-report-details')
            ->append('-')
            ->append(date('Y-m-d'))
            ->slug()
            ->append('.pdf')
            ->value();

        $contentDisposition = 'inline; filename="'.$filename.'"';

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $contentDisposition);
    }
}

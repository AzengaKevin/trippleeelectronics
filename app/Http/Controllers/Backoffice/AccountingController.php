<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Services\AccountingEntryService;
use App\Services\AccountingPeriodService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountingController extends Controller
{
    public function __construct(
        private readonly AccountingPeriodService $accountingPeriodService,
        private readonly AccountingEntryService $accountingEntryService,
    ) {}

    public function index(Request $request)
    {
        $params = $request->only('start_date', 'end_date');

        $periods = $this->accountingPeriodService->get(...$params);

        $periods->transform(fn ($period) => [
            'id' => $period->id,
            'name' => $period->name(),
            'start_date' => $period->start_date->format('Y-m-d'),
            'end_date' => $period->end_date->format('Y-m-d'),
            'status' => $period->status,
        ]);

        return Inertia::render('backoffice/accounting/IndexPage', [
            'periods' => $periods,
            'params' => $params,
        ]);
    }

    public function show(AccountingPeriod $accountingPeriod): Response
    {

        $entries = $this->accountingEntryService->get(
            accountingPeriod: $accountingPeriod,
            perPage: 24,
            with: ['account'],
        );

        return Inertia::render('backoffice/accounting/ShowPage', [
            'entries' => $entries,
            'period' => [
                ...$accountingPeriod->toArray(),
                'name' => $accountingPeriod->name(),
            ],
        ]);
    }
}

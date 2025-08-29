<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Services\ExcelService;
use App\Services\PaymentMethodService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    use ExcelService, RedirectWithFeedback;

    public function __construct(
        private SettingsService $settingsService,
        private PaymentMethodService $paymentMethodService,
    ) {}

    public function show(Request $request)
    {
        $params = $request->only('group');

        $params['group'] = data_get($params, 'group', 'general');

        $settings = $this->settingsService->get($params['group']);

        $groups = $this->settingsService->getSettingsGroups();

        $paymentMethods = $this->paymentMethodService->get(perPage: null, orderBy: 'name', orderDirection: 'asc')->map(
            fn ($method) => [
                'value' => $method->id,
                'label' => $method->name,
            ]
        );

        return Inertia::render('backoffice/settings/ShowPage', compact('settings', 'groups', 'params', 'paymentMethods'));
    }

    public function update(UpdateSettingsRequest $request)
    {

        $data = $request->validated();

        try {

            $this->settingsService->update($data, data_get(request()->only('group'), 'group', 'general'));

            return $this->sendSuccessRedirect('Settings updated successfully', route('backoffice.settings.show', [
                'group' => data_get($data, 'group', 'general'),
            ]));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Failed to update settings', $throwable);
        }
    }
}

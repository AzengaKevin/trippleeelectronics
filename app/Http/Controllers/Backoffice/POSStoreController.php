<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\Store;
use App\Services\POSStoreService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class POSStoreController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(private POSStoreService $posStoreService) {}

    public function update(Request $request)
    {
        $data = $request->validate([
            'store' => ['required', Rule::exists(Store::class, 'id')],
        ]);

        try {

            $this->posStoreService->setCurrentPOSStore($data['store']);

            activity()
                ->withProperties(['store' => $data['store']])
                ->log('Updated current POS store');

            return $this->sendSuccessRedirect('Current store updated successfully.', url()->previous());
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Could not update the current store.', $throwable);
        }
    }
}

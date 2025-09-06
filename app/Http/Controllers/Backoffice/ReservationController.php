<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    use RedirectWithFeedback;

    private User $currentUser;

    public function __construct(private readonly ReservationService $reservationService)
    {

        $this->currentUser = request()->user();
    }

    public function index(Request $request)
    {

        $params = $request->only('query');
    }

    public function store(StoreReservationRequest $storeReservationRequest)
    {

        $data = $storeReservationRequest->validated();

        try {

            $data['current_user_id'] = $this->currentUser->id;

            $reservation = DB::transaction(function () use ($data) {

                $reservation = $this->reservationService->create($data);
            });

            return $this->sendSuccessRedirect("You've successfully created a reservation", url()->previous());
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Creating reservation failed', $throwable);
        }
    }
}

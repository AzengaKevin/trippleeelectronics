<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Responses\Concerns\RedirectWithFeedback;
use App\Models\User;
use App\Services\ActivityService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    use RedirectWithFeedback;

    public function __construct(
        private readonly ActivityService $activityService,
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): Response
    {
        $params = $request->only(['query', 'causer']);

        $filters = [];

        if ($query = data_get($params, 'query')) {

            $filters['query'] = $query;
        }

        if ($causer = data_get($params, 'causer')) {

            $filters['causer'] = User::query()->find($causer);
        }

        $activities = $this->activityService->get(...$filters, with: ['causer']);

        $causers = $this->userService->getOfficials()->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
        ]);

        return Inertia::render('backoffice/activities/IndexPage', [
            'activities' => $activities,
            'causers' => $causers,
            'params' => $params,
        ]);
    }

    public function show(int $activity): Response
    {
        $activity = Activity::query()->with(['causer', 'subject'])->find($activity);

        return Inertia::render('backoffice/activities/ShowPage', [
            'activity' => $activity,
        ]);
    }

    public function destroy(int $activity): RedirectResponse
    {
        try {

            $activity = Activity::find($activity);

            $this->activityService->deleteActivity($activity);

            return $this->sendSuccessRedirect('Activity deleted successfully', route('backoffice.activities.index'));
        } catch (\Throwable $throwable) {

            return $this->sendErrorRedirect('Activity deletion failed, check logs for extra details', $throwable);
        }
    }
}

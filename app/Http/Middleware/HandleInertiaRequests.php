<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use App\Services\POSStoreService;
use App\Services\ResourceService;
use App\Services\UserService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'feedback' => fn () => $request->session()->get('feedback'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => app(UserService::class)->getCurrentUser($request->user()),
                'resources' => app(ResourceService::class)->getByUser($request->user(), $request->input('search_resource', null)),
                'permissions' => app(PermissionService::class)->getUserPermissions($request->user()),
                'store' => app(POSStoreService::class)->getCurrentPOSStore($request->user()),
                'params' => [
                    'search_resource' => $request->input('search_resource', null),
                ],
            ],
            'status' => $request->session()->get('status'),
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}

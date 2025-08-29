<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspendedEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $employee = Employee::query()->where('user_id', $user->id)->first();

        $suspension = $employee?->suspensions()?->latest()?->first();

        $message = 'Your account is currently suspended. Please contact support.';

        abort_if($employee && method_exists($employee, 'isCurrentlySuspended') && $employee->isCurrentlySuspended(), 403, $suspension?->reason ?? $message);

        return $next($request);
    }
}

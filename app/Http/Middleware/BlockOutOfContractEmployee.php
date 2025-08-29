<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockOutOfContractEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $employee = Employee::query()->where('user_id', $user->id)->first();

        $message = 'Your contract has expired. Access denied. Contact Administrator for assistance.';

        abort_if($employee && method_exists($employee, 'isOutisOutOfContract') && $employee->isOutisOutOfContract(), 403, $message);

        return $next($request);
    }
}

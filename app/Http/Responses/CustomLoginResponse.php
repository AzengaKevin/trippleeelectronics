<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse;

class CustomLoginResponse implements LoginResponse
{
    public function toResponse($request)
    {

        $user = $request->user();

        $canAccessBackoffice = $user->hasPermissionTo('access-backoffice');

        if ($canAccessBackoffice) {

            activity()
                ->causedBy($user)
                ->log('User logged in to backoffice');
        }

        return $canAccessBackoffice
            ? redirect()->intended(route('backoffice.dashboard'))
            : redirect()->intended(route('account.dashboard'));
    }
}

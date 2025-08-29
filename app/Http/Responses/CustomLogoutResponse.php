<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse;

class CustomLogoutResponse implements LogoutResponse
{
    public function toResponse($request)
    {
        return to_route('welcome');
    }
}

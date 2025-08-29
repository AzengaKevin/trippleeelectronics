<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'limit', 'perPage');

        $results = $this->userService->get(...$params);

        return UserResource::collection($results);
    }
}

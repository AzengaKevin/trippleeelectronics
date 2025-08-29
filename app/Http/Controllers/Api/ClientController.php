<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Services\ClientService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clientService) {}

    public function index(Request $request)
    {
        $params = $request->only('query', 'limit', 'perPage');

        $clients = $this->clientService->get(...$params);

        return new ClientResource($clients);
    }
}
